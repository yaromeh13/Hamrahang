package ir.hamrahang.tv.data.local

import android.content.Context
import androidx.room.Room
object DatabaseProvider {

    private lateinit var db: AppDatabase

    fun init(context: Context) {

        db = Room.databaseBuilder(
            context.applicationContext,
            AppDatabase::class.java,
            "hamrahang.db"
        )
            .fallbackToDestructiveMigration()
            .build()

    }

    fun database() = db

}
