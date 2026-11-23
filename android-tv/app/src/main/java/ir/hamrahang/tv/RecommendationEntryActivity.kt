package ir.hamrahang.tv

import android.app.Activity
import android.content.Intent
import android.os.Bundle

/**
 * Small exported trampoline used by Android TV launcher intents.
 * It forwards recommendation selections to the app's normal detail navigation.
 */
class RecommendationEntryActivity : Activity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        val mainIntent = Intent(this, MainActivity::class.java).apply {
            intent.getStringExtra(EXTRA_TYPE)?.let { putExtra(EXTRA_TYPE, it) }
            intent.getStringExtra(EXTRA_ID)?.let { putExtra(EXTRA_ID, it) }
            addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_SINGLE_TOP)
        }
        startActivity(mainIntent)
        finish()
    }

    companion object {
        const val EXTRA_TYPE = "ir.hamrahang.tv.extra.RECOMMENDATION_TYPE"
        const val EXTRA_ID = "ir.hamrahang.tv.extra.RECOMMENDATION_ID"
    }
}
