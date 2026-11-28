plugins {
    alias(libs.plugins.android.library)
    alias(libs.plugins.kotlin.android)
    alias(libs.plugins.ksp)
}

android {
    namespace = "ir.hamrahang.core"
    compileSdk = 34

    defaultConfig {
        minSdk = 21
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = "17"
    }
}

dependencies {
    // "api": these types appear in core's public classes, so both apps need them.
    api(libs.androidx.core.ktx)
    api(libs.androidx.lifecycle.viewmodel.ktx)
    api(libs.room.runtime)
    api(libs.room.ktx)
    api(libs.retrofit)
    api(libs.retrofit.gson)
    api(libs.okhttp.logging)
    api(libs.androidx.media3.exoplayer)
    api(libs.androidx.media3.exoplayer.hls)

    ksp(libs.room.compiler)
}
