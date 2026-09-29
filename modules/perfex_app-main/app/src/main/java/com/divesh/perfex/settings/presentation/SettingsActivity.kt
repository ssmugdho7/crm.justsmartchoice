package com.divesh.perfex.settings.presentation

import android.content.Intent
import android.os.Bundle
import android.widget.ArrayAdapter
import androidx.activity.viewModels
import com.divesh.perfex.R
import com.divesh.perfex.core.activities.MainActivity
import com.divesh.perfex.core.base.BaseActivity
import com.divesh.perfex.databinding.ActivitySettingsBinding
import dagger.hilt.android.AndroidEntryPoint

@AndroidEntryPoint
class SettingsActivity : BaseActivity() {
    private lateinit var binding: ActivitySettingsBinding
    private val viewModel: SettingsViewModel by viewModels()

    private var languages = arrayListOf<String>()
    override fun onPermissionsChanged(permissions: Map<String, @JvmSuppressWildcards Boolean>) {

    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivitySettingsBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayShowTitleEnabled(false)
        initViews()
        initClickListeners()
        initObservers()
    }

    private fun initClickListeners() {
        binding.selectedLanguage.setOnItemClickListener { _, _, position, _ ->
            val locale = when (position) {
                0 -> {
                    "en"
                }
                1 -> {
                    "hi"
                }
                2 -> {
                    "bn"
                }
                3 -> {
                    "de"
                }
                4 -> {
                    "pt"
                }
                5 -> {
                    "ru"
                }
                else -> {
                    "en"
                }
            }
            if (viewModel.initialProposalResponse.value != null) {
                if (viewModel.initialProposalResponse.value != locale) {
                    setLocale(locale)
                }
            } else {
                setLocale(locale)
            }
        }
    }

    private fun initObservers() {
        viewModel.initialProposalResponse.observe(this) {
            if (it != null) {
                val locale = when (it) {
                    "en" -> {
                        languages[0]
                    }
                    "hi" -> {
                        languages[1]
                    }
                    "bn" -> {
                        languages[2]
                    }
                    "de" -> {
                        languages[3]
                    }
                    "pt" -> {
                        languages[5]
                    }
                    "ru" -> {
                        languages[5]
                    }
                    else -> {
                        languages[0]
                    }
                }
                binding.selectedLanguage.setText(locale, false)
            }
        }
    }

    private fun initViews() {
        initLanguageSelection()
    }

    private fun initLanguageSelection() {
        languages = arrayListOf(
            getString(R.string.english_lang),
            getString(R.string.hindi_lang),
            getString(R.string.bengali_lang),
            getString(R.string.german_lang),
            getString(R.string.portuguese_lang),
            getString(R.string.russian_lang)
        )
        val languagesAdapter = ArrayAdapter(this, R.layout.list_item, languages)
        binding.selectedLanguage.setAdapter(languagesAdapter)
    }

    private fun setLocale(localeString: String) {
        viewModel.saveLocale(localeString)
        val settingsIntent = Intent(
            this,
            MainActivity::class.java
        ).apply {
            flags = Intent.FLAG_ACTIVITY_CLEAR_TOP
        }
        startActivity(settingsIntent)
        finish()
    }
}