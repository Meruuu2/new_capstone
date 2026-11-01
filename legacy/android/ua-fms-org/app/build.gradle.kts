plugins {
    id("com.android.application")
    id("org.jetbrains.kotlin.android")
}

android {
    namespace = "ph.edu.ua.fms.org"
    compileSdk = 35

    defaultConfig {
        applicationId = "ph.edu.ua.fms.org"
        minSdk = 24
        targetSdk = 35
        versionCode = 1
        versionName = "1.0.0"

        // Must match Laravel .env — change before release builds
        buildConfigField("String", "FMS_LOGIN_URL", "\"http://127.0.0.1:8000/fms-portal-entry\"")
        buildConfigField("String", "FMS_ACCESS_TOKEN", "\"UA-FMS-ACCESS-2025\"")
        buildConfigField("String", "FMS_ORG_SECRET", "\"UA-ORG-2025\"")
    }

    buildTypes {
        debug {
            isDebuggable = true
            // Allow http:// for LAN / local testing
            manifestPlaceholders["usesCleartext"] = "true"
        }
        release {
            isMinifyEnabled = true
            proguardFiles(
                getDefaultProguardFile("proguard-android-optimize.txt"),
                "proguard-rules.pro"
            )
            manifestPlaceholders["usesCleartext"] = "false"
        }
    }

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = "17"
    }

    buildFeatures {
        buildConfig = true
        viewBinding = true
    }
}

dependencies {
    implementation("androidx.core:core-ktx:1.15.0")
    implementation("androidx.appcompat:appcompat:1.7.0")
    implementation("com.google.android.material:material:1.12.0")
    implementation("androidx.constraintlayout:constraintlayout:2.2.0")
}
