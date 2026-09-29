package com.divesh.perfex.tasks.presentation.view.tabs.attachments

import android.app.Activity
import android.os.Bundle
import androidx.fragment.app.Fragment
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.activity.result.ActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.airbnb.lottie.LottieAnimationView
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.FragmentAttachmentsBinding
import com.divesh.perfex.tasks.domain.adapters.AttachmentsAdapter
import com.divesh.perfex.tasks.domain.interfaces.AttachmentInterface
import com.divesh.perfex.tasks.domain.models.ViewTaskResponseModel
import com.divesh.perfex.tasks.presentation.view.tabs.ViewTaskViewModel
import com.github.dhaval2404.imagepicker.ImagePicker
import dagger.hilt.android.AndroidEntryPoint
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.toRequestBody
import java.io.InputStream

@AndroidEntryPoint
class AttachmentsFragment : Fragment(), AttachmentInterface {
    private var _binding: FragmentAttachmentsBinding? = null
    private val binding get() = _binding!!
    private val viewModel: ViewTaskViewModel by viewModels()
    private lateinit var lottieAnimation: LottieAnimationView
    private val listAdapter = AttachmentsAdapter(arrayListOf(), this)
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
                            if (context != null) {
                                fileName =
                                    Extensions().getFileNameFromPickedImageData(
                                        it,
                                        requireContext()
                                    )
                                fileInputStream = context?.contentResolver?.openInputStream(it)
                                uploadImage()
                            }
                        }
                    }
                }
                ImagePicker.RESULT_ERROR -> {
                    Toast.makeText(context, ImagePicker.getError(data), Toast.LENGTH_SHORT).show()
                }
                else -> {
                    Toast.makeText(context, "Cancelled", Toast.LENGTH_SHORT).show()
                }
            }
        }

    private fun uploadImage() {
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
        viewModel.addAttachment(
            arguments?.getString("id").toString()
                .toRequestBody("multipart/form-data".toMediaTypeOrNull()),
            multipartImage
        )
    }

    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentAttachmentsBinding.inflate(inflater, container, false)
        observeViewModel()
        initViews()
        initListeners()
        return binding.root
    }

    private fun initViews() {
        lottieAnimation = binding.root.findViewById(R.id.noDataFound)
        binding.usersList.apply {
            layoutManager = LinearLayoutManager(context)
            adapter = listAdapter
        }

        binding.swipeRefresh.setOnRefreshListener {
            viewModel.loadViewTaskInitialData(arguments?.getString("id").toString().toInt())
            binding.swipeRefresh.isRefreshing = false
        }
    }

    private fun observeViewModel() {
        viewModel.loadViewTaskInitialData(arguments?.getString("id").toString().toInt())
        viewModel.internetProblem.observe(viewLifecycleOwner) {
            if (it != null) {
                Extensions().showMessage(binding.root,  getString(R.string.no_internet))
            }
        }
        viewModel.responseMessage.observe(viewLifecycleOwner) {
            if (it != null) {
                Extensions().showMessage(binding.root, it)
            }
        }
        viewModel.attachmentAdded.observe(viewLifecycleOwner) {
            if (it != null) {
                if (it) {
                    viewModel.loadViewTaskInitialData(arguments?.getString("id").toString().toInt())
                }
            }
        }
        viewModel.loading.observe(viewLifecycleOwner) {
            if (it) {
                binding.progressBar.visibility = View.VISIBLE
            } else {
                binding.progressBar.visibility = View.GONE
            }
        }
        viewModel.viewTaskResponse.observe(viewLifecycleOwner) {
            if (it != null) {
                if (it.task.attachments != null && it.task.attachments.isNotEmpty()) {
                    listAdapter.setRecords(
                        it.task.attachments,
                        arguments?.getString("id").toString()
                    )
                    binding.usersList.visibility = View.VISIBLE
                    lottieAnimation.visibility = View.GONE
                } else {
                    lottieAnimation.visibility = View.VISIBLE
                    binding.usersList.visibility = View.GONE
                }
            } else {
                lottieAnimation.visibility = View.VISIBLE
                binding.usersList.visibility = View.GONE
            }
        }
    }

    override fun onAttachmentClicked(attachment: ViewTaskResponseModel.Task.Attachment) {

    }

    override fun onAttachmentRemoved(attachment: ViewTaskResponseModel.Task.Attachment) {
        Extensions().simpleAlert(
            getString(R.string.confirm_deletion),
            requireContext()
        ) { isConfirmed ->
            if (isConfirmed) {
                viewModel.removeAttachment(
                    attachment.id,
                    arguments?.getString("id").toString().toInt()
                )
            }
        }
    }

    private fun initListeners() {
        binding.addAttachment.setOnClickListener {
            ImagePicker.with(this).galleryMimeTypes(  //Exclude gif images
                mimeTypes = arrayOf(
                    "image/png", "image/jpg", "image/jpeg"
                )
            ).saveDir(binding.root.context.getExternalFilesDir(null)!!)
                .compress(4028) //Final image size will be less than 4 MB(Optional)
                .createIntent { intent ->
                    chooseImageLauncher.launch(intent)
                }
        }
    }
}