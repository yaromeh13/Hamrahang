package ir.hamrahang.app

import android.os.Bundle
import androidx.appcompat.app.AppCompatActivity
import ir.hamrahang.app.databinding.ActivityMainBinding

class MainActivity : AppCompatActivity() {

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(ActivityMainBinding.inflate(layoutInflater).root)
    }
}
