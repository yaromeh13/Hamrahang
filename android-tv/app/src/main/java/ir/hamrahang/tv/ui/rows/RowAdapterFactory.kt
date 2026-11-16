package ir.hamrahang.tv.ui.rows

import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.TextView
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import ir.hamrahang.tv.R
import ir.hamrahang.tv.ui.AlbumAdapter
import ir.hamrahang.tv.ui.SongAdapter
import ir.hamrahang.tv.ui.MusicVideoAdapter
/*
class ContentRowsAdapter(

    private val rows: List<ContentRow>

) : RecyclerView.Adapter<ContentRowsAdapter.RowHolder>() {

    class RowHolder(
        view: View
    ) : RecyclerView.ViewHolder(view) {

        val title: TextView =
            view.findViewById(R.id.txtRowTitle)

        val recycler: RecyclerView =
            view.findViewById(R.id.recyclerRow)
    }

    override fun onCreateViewHolder(
        parent: ViewGroup,
        viewType: Int
    ): RowHolder {

        val view = LayoutInflater.from(parent.context)
            .inflate(
                R.layout.item_content_row,
                parent,
                false
            )

        return RowHolder(view)
    }

    override fun getItemCount() =
        rows.size

    override fun onBindViewHolder(
        holder: RowHolder,
        position: Int
    ) {

        val row = rows[position]

        if (row.title.isNullOrBlank()) {
            holder.title.visibility = View.GONE
        } else {
            holder.title.visibility = View.VISIBLE
            holder.title.text = row.title
        }

        holder.recycler.layoutManager =
            LinearLayoutManager(
                holder.itemView.context,
                RecyclerView.HORIZONTAL,
                false
            )

        when (row) {

            is ContentRow.Songs -> {

                holder.recycler.adapter =
                    SongAdapter(row.items)
            }

            is ContentRow.Videos -> {

                holder.recycler.adapter =
                    MusicVideoAdapter(row.items)
            }

            is ContentRow.Albums -> {

                holder.recycler.adapter =
                    AlbumAdapter(row.items)
            }
        }
    }
}*/