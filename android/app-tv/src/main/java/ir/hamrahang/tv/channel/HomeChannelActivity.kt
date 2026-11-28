package ir.hamrahang.tv.channel

import android.app.Activity
import android.content.Intent
import android.os.Bundle
import ir.hamrahang.tv.MainActivity

/**
 * Exported entry for tiles on the Google TV / Android TV home row.
 * MainActivity stays private; this activity only forwards the click.
 */
class HomeChannelActivity : Activity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        startActivity(
            Intent(this, MainActivity::class.java).apply {
                intent?.extras?.let(::putExtras)
                flags = Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP
            }
        )
        finish()
    }
}
