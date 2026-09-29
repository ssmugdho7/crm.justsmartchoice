package com.divesh.perfex.settings.presentation

import android.content.SharedPreferences
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.divesh.perfex.core.constants.Constants
import dagger.hilt.android.lifecycle.HiltViewModel
import javax.inject.Inject

@HiltViewModel
class SettingsViewModel  @Inject constructor(
    private val preferencesFile: SharedPreferences,
) : ViewModel() {
    val initialProposalResponse = MutableLiveData<String>()
    init {
        loadInitialSettings()
    }

    private fun loadInitialSettings() {
        initialProposalResponse.value = preferencesFile.getString(Constants.defaultLanguage, "en")
    }

    fun saveLocale(locale: String) {
        preferencesFile.edit().putString(Constants.defaultLanguage, locale).apply()
    }
}