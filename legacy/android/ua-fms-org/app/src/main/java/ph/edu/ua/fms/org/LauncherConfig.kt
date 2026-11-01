package ph.edu.ua.fms.org

import java.net.URLEncoder

object LauncherConfig {
    const val ROLE = "org_staff"

    fun portalUrl(): String {
        val base = BuildConfig.FMS_LOGIN_URL.trimEnd('/')
        val token = URLEncoder.encode(BuildConfig.FMS_ACCESS_TOKEN, Charsets.UTF_8.name())
        val role = URLEncoder.encode(ROLE, Charsets.UTF_8.name())
        return "$base?access_token=$token&role=$role"
    }

    fun isValidOrgKey(input: String): Boolean {
        return input.trim() == BuildConfig.FMS_ORG_SECRET
    }
}
