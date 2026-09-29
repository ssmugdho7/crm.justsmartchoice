package com.divesh.perfex.core.base

import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import kotlinx.coroutines.Job

open class BaseViewModel: ViewModel() {
    protected var job: Job? = null
    val loading = MutableLiveData<Boolean>()
    val internetProblem = MutableLiveData<Boolean>()
    val filteredCount = MutableLiveData<Int>()
    val totalCount = MutableLiveData<Int>()
    val itemDeletedAtPosition = MutableLiveData<Int>()
    val itemAdded = MutableLiveData<Boolean>()

    val responseBody = MutableLiveData<Any>()
    val responseMessage = MutableLiveData<String?>()
    protected val originalRecords = MutableLiveData<List<Any>>()
    val currentRecords = MutableLiveData<List<Any>>()
    protected var tempRecords: List<Any> = ArrayList()
}