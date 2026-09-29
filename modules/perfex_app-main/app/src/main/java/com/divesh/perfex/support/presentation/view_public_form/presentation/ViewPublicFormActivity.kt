package com.divesh.perfex.support.presentation.view_public_form.presentation

import android.app.Activity
import android.content.ContentResolver
import android.content.Context
import android.net.Uri
import android.os.Bundle
import android.provider.OpenableColumns
import android.view.View
import android.widget.Button
import android.widget.ImageView
import android.widget.ProgressBar
import android.widget.Toast
import androidx.activity.result.ActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.activity.viewModels
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import com.airbnb.lottie.LottieAnimationView
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.ActivityViewPublicFormBinding
import com.divesh.perfex.support.domain.adapters.TicketRepliesAdapter
import com.divesh.perfex.support.domain.models.SupportResponseModel
import com.github.dhaval2404.imagepicker.ImagePicker
import dagger.hilt.android.AndroidEntryPoint
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.toRequestBody
import java.io.File
import java.io.InputStream

@AndroidEntryPoint
class ViewPublicFormActivity : AppCompatActivity() {
    private lateinit var binding: ActivityViewPublicFormBinding
    private val viewModel: ViewPublicFormViewModel by viewModels()
    private lateinit var lottieAnimation: LottieAnimationView
    private val listAdapter = TicketRepliesAdapter(arrayListOf())
    private lateinit var progressBar: ProgressBar
    private var ticket: SupportResponseModel.Ticket? = null
    private var fileName: String? = null
    private var fileInputStream: InputStream? = null
    private var chooseImageLauncher =
        registerForActivityResult(ActivityResultContracts.StartActivityForResult()) { result: ActivityResult ->
            val resultCode = result.resultCode
            val data = result.data
            when (resultCode) {
                Activity.RESULT_OK -> {
                    if (data != null) {
                        data.data?.let {
                            fileName = getFileName(it)
                            fileInputStream = contentResolver.openInputStream(it)
                        }
                    }
                }
                ImagePicker.RESULT_ERROR -> {
                    Toast.makeText(this, ImagePicker.getError(data), Toast.LENGTH_SHORT).show()
                }
                else -> {
                    Toast.makeText(this, "Cancelled", Toast.LENGTH_SHORT).show()
                }
            }
        }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        checkTicket()
        binding = ActivityViewPublicFormBinding.inflate(layoutInflater)
        setContentView(binding.root)
        setSupportActionBar(binding.toolbar)
        supportActionBar!!.setDisplayShowTitleEnabled(false)
        observeViewModel()
        initViews()
        initClickListeners()
    }

    private fun initClickListeners() {
        binding.toolbar.findViewById<ImageView>(R.id.toolbarBack).setOnClickListener {
            onBackPressedDispatcher.onBackPressed()
        }
        binding.chooseFileBtn.setOnClickListener {
            ImagePicker.with(this).cropSquare().galleryMimeTypes(  //Exclude gif images
                mimeTypes = arrayOf(
                    "image/png", "image/jpg", "image/jpeg"
                )
            ).saveDir(binding.root.context.getExternalFilesDir(null)!!)
                .compress(1024) //Final image size will be less than 1 MB(Optional)
                .maxResultSize(
                    512, 512
                )    //Final image resolution will be less than 1080 x 1080(Optional)
                .createIntent { intent ->
                    chooseImageLauncher.launch(intent)
                }
        }
        binding.toolbar.findViewById<Button>(R.id.toolbarAddReplyBtn).setOnClickListener {
            if (validateForm()) {
                if (ticket != null) {
                    var multipartImage: MultipartBody.Part? = null
                    if (fileInputStream != null) {
                        val requestFile = Extensions().getBytesFromInputStream(fileInputStream)?.toRequestBody()
                        if (requestFile != null) {
                            multipartImage = MultipartBody.Part.createFormData(
                                "attachments[]",
                                fileName,
                                requestFile
                            )
                        }
                    }
                    viewModel.addTicketReply(
                        ticket!!.ticketid.toString()
                            .toRequestBody("multipart/form-data".toMediaTypeOrNull()),
                        binding.reply.editText?.text.toString()
                            .toRequestBody("multipart/form-data".toMediaTypeOrNull()),
                        multipartImage
                    )
                }
            }
        }
    }

    private fun initViews() {
        progressBar = binding.progressBar
        lottieAnimation = binding.noDataFound
        binding.usersList.apply {
            layoutManager = LinearLayoutManager(context)
            adapter = listAdapter
        }
        binding.swipeRefresh.setOnRefreshListener {
            ticket?.ticketid?.let { viewModel.refresh(it) }
            binding.swipeRefresh.isRefreshing = false
        }
        ticket?.ticketid?.let { viewModel.refresh(it) }
    }

    private fun observeViewModel() {
        viewModel.currentRecords.observe(this) { tickets ->
            if (tickets != null && tickets.isNotEmpty()) {
                listAdapter.updateList(tickets)
                binding.noDataFound.visibility = View.GONE
                binding.swipeRefresh.visibility = View.VISIBLE
            } else {
                binding.noDataFound.visibility = View.VISIBLE
                binding.swipeRefresh.visibility = View.GONE
            }
        }

        viewModel.loading.observe(this) { isLoading ->
            isLoading?.let {
                if (isLoading) {
                    progressBar.visibility = View.VISIBLE
                } else {
                    progressBar.visibility = View.GONE
                }
            }
        }

        viewModel.responseMessage.observe(this) {
            if (it != null) {
                Extensions().showMessage(binding.root, it)
            }
        }

        viewModel.internetProblem.observe(this) {
            if (it != null) {
                Toast.makeText(this, getString(R.string.no_internet), Toast.LENGTH_SHORT).show()
            }
        }

        viewModel.replyAdded.observe(this) {
            if (it != null) {
                if (it) {
                    fileInputStream = null
                    fileName = null
                    binding.reply.editText?.setText("")
                    ticket?.ticketid?.let { ticketId -> viewModel.refresh(ticketId) }
                }
            }
        }
    }

    private fun validateForm(): Boolean {
        var isValid = true
        if (binding.reply.editText?.text.isNullOrEmpty()) {
            isValid = false
            binding.reply.editText?.error = getString(R.string.field_required)
        }
        return isValid
    }

    private fun checkTicket() {
        ticket = intent.getSerializableExtra("ticket") as SupportResponseModel.Ticket?
        if (ticket == null) {
            onBackPressedDispatcher.onBackPressed()
        }
    }

    private fun Context.getFileName(uri: Uri): String? = when (uri.scheme) {
        ContentResolver.SCHEME_CONTENT -> getContentFileName(uri)
        else -> uri.path?.let(::File)?.name
    }

    private fun Context.getContentFileName(uri: Uri): String? = runCatching {
        contentResolver.query(uri, null, null, null, null)?.use { cursor ->
            cursor.moveToFirst()
            return@use cursor.getColumnIndexOrThrow(OpenableColumns.DISPLAY_NAME)
                .let(cursor::getString)
        }
    }.getOrNull()
}