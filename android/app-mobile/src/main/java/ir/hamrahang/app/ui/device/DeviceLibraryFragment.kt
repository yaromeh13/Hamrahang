package ir.hamrahang.app.ui.device

import android.Manifest
import android.content.ContentUris
import android.content.pm.PackageManager
import android.os.Build
import android.os.Bundle
import android.provider.MediaStore
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.activity.result.contract.ActivityResultContracts
import androidx.core.content.ContextCompat
import androidx.core.view.isVisible
import androidx.fragment.app.Fragment
import androidx.lifecycle.lifecycleScope
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.google.android.material.button.MaterialButton
import ir.hamrahang.app.R
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

/** Music and videos already stored on this phone. */
class DeviceLibraryFragment : Fragment(R.layout.fragment_device_library) {

    private var list: RecyclerView? = null
    private var messageBox: View? = null
    private var messageText: TextView? = null
    private var allowButton: MaterialButton? = null
    private lateinit var adapter: TrackAdapter
    private val permissionRequest = registerForActivityResult(
        ActivityResultContracts.RequestMultiplePermissions()
    ) { load() }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        list = view.findViewById(R.id.deviceList)
        messageBox = view.findViewById(R.id.deviceMessage)
        messageText = view.findViewById(R.id.deviceMessageText)
        allowButton = view.findViewById(R.id.deviceAllow)

        adapter = TrackAdapter { track ->
            val items = adapter.tracks
            val index = items.indexOf(track)
            if (index >= 0) DeviceQueue.play(requireContext(), items, index)
        }
        list?.layoutManager = LinearLayoutManager(requireContext())
        list?.adapter = adapter
        allowButton?.setOnClickListener { requestAccess() }
        load()
    }

    override fun onDestroyView() {
        list = null
        messageBox = null
        messageText = null
        allowButton = null
        super.onDestroyView()
    }

    private fun requiredPermissions(): Array<String> =
        if (Build.VERSION.SDK_INT >= 33) {
            arrayOf(Manifest.permission.READ_MEDIA_AUDIO, Manifest.permission.READ_MEDIA_VIDEO)
        } else {
            arrayOf(Manifest.permission.READ_EXTERNAL_STORAGE)
        }

    private fun hasAccess(): Boolean =
        requiredPermissions().any {
            ContextCompat.checkSelfPermission(requireContext(), it) == PackageManager.PERMISSION_GRANTED
        }

    private fun requestAccess() {
        permissionRequest.launch(requiredPermissions())
    }

    private fun load() {
        if (!hasAccess()) {
            list?.isVisible = false
            messageBox?.isVisible = true
            messageText?.setText(R.string.device_permission)
            allowButton?.isVisible = true
            return
        }
        viewLifecycleOwner.lifecycleScope.launch {
            val tracks = withContext(Dispatchers.IO) { queryTracks() }
            if (!isAdded) return@launch
            adapter.submit(tracks)
            val empty = tracks.isEmpty()
            list?.isVisible = !empty
            messageBox?.isVisible = empty
            allowButton?.isVisible = false
            if (empty) messageText?.setText(R.string.device_empty)
        }
    }

    private fun queryTracks(): List<DeviceTrack> {
        val music = if (canReadAudio()) {
            query(
                uri = MediaStore.Audio.Media.EXTERNAL_CONTENT_URI,
                titleColumn = MediaStore.Audio.Media.TITLE,
                subtitleColumn = MediaStore.Audio.Media.ARTIST,
                video = false
            )
        } else emptyList()
        val videos = if (canReadVideo()) {
            query(
                uri = MediaStore.Video.Media.EXTERNAL_CONTENT_URI,
                titleColumn = MediaStore.Video.Media.TITLE,
                subtitleColumn = MediaStore.Video.Media.BUCKET_DISPLAY_NAME,
                video = true
            )
        } else emptyList()
        return (music + videos).sortedBy { it.title.lowercase() }
    }

    private fun canReadAudio(): Boolean =
        if (Build.VERSION.SDK_INT >= 33) {
            ContextCompat.checkSelfPermission(requireContext(), Manifest.permission.READ_MEDIA_AUDIO) ==
                PackageManager.PERMISSION_GRANTED
        } else {
            ContextCompat.checkSelfPermission(requireContext(), Manifest.permission.READ_EXTERNAL_STORAGE) ==
                PackageManager.PERMISSION_GRANTED
        }

    private fun canReadVideo(): Boolean =
        if (Build.VERSION.SDK_INT >= 33) {
            ContextCompat.checkSelfPermission(requireContext(), Manifest.permission.READ_MEDIA_VIDEO) ==
                PackageManager.PERMISSION_GRANTED
        } else {
            ContextCompat.checkSelfPermission(requireContext(), Manifest.permission.READ_EXTERNAL_STORAGE) ==
                PackageManager.PERMISSION_GRANTED
        }

    private fun query(
        uri: android.net.Uri,
        titleColumn: String,
        subtitleColumn: String,
        video: Boolean
    ): List<DeviceTrack> {
        val current = context ?: return emptyList()
        val projection = arrayOf(MediaStore.MediaColumns._ID, titleColumn, subtitleColumn)
        return runCatching {
            current.contentResolver.query(uri, projection, null, null, null)?.use { cursor ->
                val idIndex = cursor.getColumnIndexOrThrow(MediaStore.MediaColumns._ID)
                val titleIndex = cursor.getColumnIndexOrThrow(titleColumn)
                val subtitleIndex = cursor.getColumnIndex(subtitleColumn)
                buildList {
                    while (cursor.moveToNext()) {
                        val id = cursor.getLong(idIndex)
                        val title = cursor.getString(titleIndex)?.takeIf { it.isNotBlank() } ?: continue
                        val detail = if (subtitleIndex >= 0) cursor.getString(subtitleIndex).orEmpty() else ""
                        val kind = current.getString(if (video) R.string.device_videos else R.string.device_music)
                        add(
                            DeviceTrack(
                                uri = ContentUris.withAppendedId(uri, id),
                                title = title,
                                subtitle = if (detail.isBlank()) kind else "$kind · $detail",
                                video = video
                            )
                        )
                    }
                }
            }.orEmpty()
        }.getOrDefault(emptyList())
    }

    private class TrackAdapter(
        private val onClick: (DeviceTrack) -> Unit
    ) : RecyclerView.Adapter<TrackAdapter.Holder>() {

        var tracks: List<DeviceTrack> = emptyList()
            private set

        fun submit(items: List<DeviceTrack>) {
            tracks = items
            notifyDataSetChanged()
        }

        override fun onCreateViewHolder(parent: ViewGroup, viewType: Int): Holder {
            val view = LayoutInflater.from(parent.context)
                .inflate(R.layout.item_device_track, parent, false)
            return Holder(view, onClick)
        }

        override fun onBindViewHolder(holder: Holder, position: Int) {
            holder.bind(tracks[position])
        }

        override fun getItemCount(): Int = tracks.size

        private class Holder(
            view: View,
            private val onClick: (DeviceTrack) -> Unit
        ) : RecyclerView.ViewHolder(view) {
            private val title = view.findViewById<TextView>(R.id.deviceTitle)
            private val subtitle = view.findViewById<TextView>(R.id.deviceSubtitle)
            private var track: DeviceTrack? = null

            init {
                view.setOnClickListener { track?.let(onClick) }
            }

            fun bind(item: DeviceTrack) {
                track = item
                title.text = item.title
                subtitle.text = item.subtitle
            }
        }
    }
}
