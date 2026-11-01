-keepclassmembers class * {
    @android.webkit.JavascriptInterface <methods>;
}

# =====================================================
# Security & Encryption
# =====================================================
-keep class javax.crypto.** { *; }
-keep class java.security.** { *; }
-keep class androidx.security.** { *; }

# =====================================================
# Network & API Protection
# =====================================================
-keep class okhttp3.** { *; }
-keep interface okhttp3.** { *; }
-dontwarn okhttp3.**
-dontwarn okio.**

# =====================================================
# JSON & Serialization
# =====================================================
-keep class com.google.gson.** { *; }
-keep interface com.google.gson.** { *; }

# =====================================================
# Kotlin Coroutines
# =====================================================
-keep class kotlinx.coroutines.** { *; }
-keep interface kotlinx.coroutines.** { *; }

# =====================================================
# Reflection Protection
# =====================================================
-dontskipnonpubliclibraryclasses
-dontskipnonpubliclibraryclassmembers

# =====================================================
# Aggressive Obfuscation Settings
# =====================================================
-optimizationpasses 5
-verbose
-allowaccessmodification
-mergeinterfacesaggressively

# =====================================================
# Remove logging in release builds
# =====================================================
-assumenosideeffects class android.util.Log {
    public static *** d(...);
    public static *** v(...);
    public static *** i(...);
}

# =====================================================
# Preserve Application Classes
# =====================================================
-keep class ph.edu.ua.fms.org.** { *; }
