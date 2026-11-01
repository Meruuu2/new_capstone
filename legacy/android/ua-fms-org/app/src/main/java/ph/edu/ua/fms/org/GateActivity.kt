package ph.edu.ua.fms.org

import android.content.Intent
import android.os.Bundle
import android.view.inputmethod.EditorInfo
import androidx.appcompat.app.AppCompatActivity
import ph.edu.ua.fms.org.databinding.ActivityGateBinding

class GateActivity : AppCompatActivity() {

    private lateinit var binding: ActivityGateBinding

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityGateBinding.inflate(layoutInflater)
        setContentView(binding.root)

        binding.keyInput.setOnEditorActionListener { _, actionId, _ ->
            if (actionId == EditorInfo.IME_ACTION_DONE) {
                attemptUnlock()
                true
            } else {
                false
            }
        }

        binding.continueButton.setOnClickListener { attemptUnlock() }
    }

    private fun attemptUnlock() {
        val key = binding.keyInput.text?.toString().orEmpty()
        if (LauncherConfig.isValidOrgKey(key)) {
            binding.errorText.visibility = android.view.View.GONE
            startActivity(
                Intent(this, PortalActivity::class.java).apply {
                    putExtra(PortalActivity.EXTRA_PORTAL_URL, LauncherConfig.portalUrl())
                }
            )
            finish()
        } else {
            binding.errorText.visibility = android.view.View.VISIBLE
            binding.keyInput.text?.clear()
            binding.keyInput.requestFocus()
        }
    }
}
