package ir.hamrahang.tv.ui

import android.animation.ObjectAnimator
import android.content.Context
import android.os.Bundle
import android.view.View
import android.animation.ValueAnimator
import android.content.Intent
import android.util.Log
import android.view.animation.DecelerateInterpolator
import android.widget.FrameLayout
import android.widget.ImageView
import androidx.fragment.app.Fragment
import android.widget.LinearLayout
import androidx.constraintlayout.widget.ConstraintLayout
import android.view.animation.LinearInterpolator
import androidx.lifecycle.lifecycleScope
import com.bumptech.glide.Glide
import ir.hamrahang.tv.R
import ir.hamrahang.tv.player.PlaybackState
import ir.hamrahang.tv.player.PlayerManager
import ir.hamrahang.tv.player.PlayerState
import kotlinx.coroutines.flow.collectLatest
import kotlinx.coroutines.flow.distinctUntilChanged
import kotlinx.coroutines.flow.map
import kotlinx.coroutines.launch

class NavFragment : Fragment(R.layout.fragment_nav) {
    interface NavCallback {
        fun onMenuSelected(menuId: Int)
        fun onRequestMainFocus()
      }
    private lateinit var imgMiniCover: ImageView
    private lateinit var btnMiniPlayPause: ImageView
    private lateinit var btnMiniFullscreen: ImageView
    private var coverAnimator: ObjectAnimator? = null
    private lateinit var sideMenu: LinearLayout
    private var callback: NavCallback? = null
    //private var isExpanded = false

    private lateinit var logoContainer: View
    private lateinit var playerMiniContainer: View
    private val focusListener =
        android.view.ViewTreeObserver.OnGlobalFocusChangeListener { _, newFocus ->
            Log.d(
                "FOCUS",
                "focus = ${newFocus?.javaClass?.simpleName} id=${newFocus?.id}"
            )
            val inside = newFocus != null && isDescendantOf(sideMenu, newFocus)
            Log.d("FOCUS", "insideMenu = $inside")
            if (inside) {
                expand()
            } else {
                collapseMenuFromOutside()
            }
        }

    override fun onAttach(context: Context) {
        super.onAttach(context)
        callback = context as? NavCallback
    }

    override fun onViewCreated(view: View, savedInstanceState: Bundle?) {
        sideMenu = view.findViewById(R.id.sideMenu)
        sideMenu.viewTreeObserver.addOnGlobalFocusChangeListener(focusListener)
        super.onViewCreated(view, savedInstanceState)
        setupSideMenuWidth()
        logoContainer = view.findViewById(R.id.logoContainer)
        playerMiniContainer = view.findViewById(R.id.playerMiniContainer)
        //miniPlayer = MiniPlayerController(playerMiniContainer)
        imgMiniCover = view.findViewById(R.id.btnMiniCover)
        btnMiniPlayPause = view.findViewById(R.id.btnMiniPlayPause)
        btnMiniPlayPause.setOnClickListener { PlayerManager.toggle() }
        btnMiniFullscreen = view.findViewById(R.id.btnMiniFullscreen)
        btnMiniFullscreen.setOnClickListener { UiNavigator.openFullscreenPlayer() }

        view.isFocusableInTouchMode = true
        view.requestFocus()

        val items = listOf(
            R.id.menuHome,
            R.id.menuLive,
            R.id.menuSearch,
            R.id.menuKids,
            R.id.menuCategories,
            R.id.menuProfile,
            R.id.menuSettings
        ).map { view.findViewById<View>(it) }

        items.forEach { item ->
            item.setOnClickListener {
                callback?.onMenuSelected(it.id)
                updateIndicator(view, it.id)
                collapseMenuFromOutside()
                view.postDelayed({
                    callback?.onRequestMainFocus()
                }, 250)
            }
        }

        updateIndicator(view, R.id.menuHome)
        //view.findViewById<View>(R.id.menuHome).requestFocus()

        observePlayer()
        imgMiniCover.setOnClickListener { UiNavigator.openPlayback() }

    }
    private fun isDescendantOf(parent: View, child: View): Boolean {
        var current: View? = child
        while (current != null) {
            if (current == parent) return true
            current = current.parent as? View
        }
        return false
    }
    private fun isMenuExpanded(): Boolean {

        val navContainer =
            activity?.findViewById<FrameLayout>(R.id.nav_fragment)
                ?: return false

        val params =
            navContainer.layoutParams as ConstraintLayout.LayoutParams

        return params.matchConstraintPercentWidth > 0.10f
    }
    private fun setupSideMenuWidth() {

        sideMenu.post {

            if (!isAdded) return@post
            val activity = activity ?: return@post
            val navContainer = activity.findViewById<View>(R.id.nav_fragment)
            val parent = navContainer.parent as? View ?: return@post
            val expandedWidth = (parent.width * 0.16f).toInt()

            sideMenu.layoutParams.width = expandedWidth
            sideMenu.requestLayout()
            //isExpanded = true
        }
    }

    // ===== EXPAND =====
    private fun expand() {
        Log.d("MENU", "expand()")
        //if (isExpanded) return
        //isExpanded = true
        if (isMenuExpanded()) return
        animateNavPercent(0.05f, 0.16f)
        btnMiniPlayPause.visibility = View.VISIBLE
        btnMiniFullscreen.visibility = View.VISIBLE
    }

    // ===== COLLAPSE =====
    fun collapseMenuFromOutside() {
        Log.d("MENU", "collapse()")
        //if (!isExpanded) return
        //isExpanded = false
        if (!isMenuExpanded()) return
        animateNavPercent(0.16f, 0.05f)
        btnMiniPlayPause.visibility = View.GONE
        btnMiniFullscreen.visibility = View.GONE
    }

    // ===== ONLY WIDTH CONTROLLER (SAFE) =====
    private fun animateNav(start: Int, end: Int) {

        val nav = view ?: return

        val anim = ValueAnimator.ofInt(start, end)
        anim.duration = 200
        anim.interpolator = DecelerateInterpolator()

        anim.addUpdateListener {
            val value = it.animatedValue as Int
            val params = nav.layoutParams
            params.width = value
            nav.layoutParams = params
        }
        anim.start()
    }

    private fun animateNavPercent(start: Float, end: Float) {
        Log.d("MENU", "animate $start -> $end")
        val navContainer = activity
            ?.findViewById<FrameLayout>(R.id.nav_fragment)
            ?: return

        val animator = ValueAnimator.ofFloat(start, end)

        animator.duration = 200
        animator.interpolator = DecelerateInterpolator()

        animator.addUpdateListener {
            val percent = it.animatedValue as Float
            val params = navContainer.layoutParams as ConstraintLayout.LayoutParams
            params.matchConstraintPercentWidth = percent
            navContainer.layoutParams = params
        }
        animator.start()
    }

    // ===== INDICATOR =====
    private fun updateIndicator(root: View, selectedId: Int) {

        val map = mapOf(
            R.id.menuHome to R.id.indicatorHome,
            R.id.menuLive to R.id.indicatorLive,
            R.id.menuSearch to R.id.indicatorSearch,
            R.id.menuSettings to R.id.indicatorSetting,
            R.id.menuKids to R.id.indicatorKids,
            R.id.menuCategories to R.id.indicatorCategories,
            R.id.menuProfile to R.id.indicatorProfile
        )

        map.values.forEach { root.findViewById<View>(it).alpha = 0f }

        map[selectedId]?.let { root.findViewById<View>(it).alpha = 1f }
    }

    private var miniPlayerInitialized = false

    private fun showMiniPlayer() {
        if (miniPlayerInitialized) return
        miniPlayerInitialized = true
        playerMiniContainer.visibility = View.VISIBLE
    }
    private fun observePlayer() {

        viewLifecycleOwner.lifecycleScope.launch {
            PlayerState.uiState.collectLatest { ui ->

                val isPlayingSession =
                    ui.playbackState != PlaybackState.IDLE

                playerMiniContainer.visibility =
                    if (isPlayingSession) View.VISIBLE
                    else View.GONE

                if (!isPlayingSession) {
                    stopCoverRotation()
                    return@collectLatest
                }

                btnMiniPlayPause.setImageResource(
                    if (ui.playbackState == PlaybackState.PLAYING)
                        R.drawable.ic_pause
                    else
                        R.drawable.ic_play_arrow
                )

                if (ui.playbackState == PlaybackState.PLAYING) {
                    startCoverRotation()
                } else {
                    stopCoverRotation()
                }
            }
        }

        viewLifecycleOwner.lifecycleScope.launch {
            PlayerState.playbackInfo.collectLatest { info ->

                Glide.with(this@NavFragment)
                    .load(info.artwork)
                    .circleCrop()
                    .into(imgMiniCover)
            }
        }
    }
/*
    private fun observePlayer() {

        // بخش داینامیک: وضعیت پخش، چرخش کاور
        viewLifecycleOwner.lifecycleScope.launch {
            PlayerState.uiState.collectLatest { ui ->

                if (ui.playbackState == PlaybackState.IDLE) {
                    playerMiniContainer.visibility = View.GONE
                    return@collectLatest
                }

                showMiniPlayer()

                btnMiniPlayPause.setImageResource(
                    if (ui.playbackState == PlaybackState.PLAYING)
                        R.drawable.ic_pause
                    else
                        R.drawable.ic_play_arrow
                )

                if (ui.playbackState == PlaybackState.PLAYING) {
                    startCoverRotation()
                } else {
                    stopCoverRotation()
                }
            }
        }

        // بخش استاتیک: عکس کاور
        viewLifecycleOwner.lifecycleScope.launch {
            PlayerState.playbackInfo.collectLatest { info ->

                Glide.with(this@NavFragment)
                    .load(info.artwork)
                    .circleCrop()
                    .into(imgMiniCover)
            }
        }
    }*/
    private fun startCoverRotation() {

        if (coverAnimator == null) {

            coverAnimator =
                ObjectAnimator.ofFloat(
                    imgMiniCover,
                    View.ROTATION,
                    0f,
                    360f
                ).apply {

                    duration = 12000
                    repeatCount = ValueAnimator.INFINITE
                    interpolator = LinearInterpolator()

                    start()
                    pause()
                }
        }

        coverAnimator?.resume()
    }

    private fun stopCoverRotation() {
        coverAnimator?.pause()
        //coverAnimator?.cancel()
        //coverAnimator = null
    }

    override fun onDestroyView() {

        if (::sideMenu.isInitialized) {
            sideMenu.viewTreeObserver.removeOnGlobalFocusChangeListener(focusListener)
        }
        super.onDestroyView()
    }

}