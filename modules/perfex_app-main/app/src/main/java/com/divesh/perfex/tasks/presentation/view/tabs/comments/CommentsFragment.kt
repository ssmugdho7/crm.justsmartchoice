package com.divesh.perfex.tasks.presentation.view.tabs.comments

import android.app.Activity
import android.os.Bundle
import androidx.fragment.app.Fragment
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Toast
import androidx.activity.result.ActivityResult
import androidx.activity.result.contract.ActivityResultContracts
import androidx.core.text.parseAsHtml
import androidx.fragment.app.viewModels
import androidx.recyclerview.widget.LinearLayoutManager
import com.airbnb.lottie.LottieAnimationView
import com.divesh.perfex.R
import com.divesh.perfex.core.helpers.Extensions
import com.divesh.perfex.databinding.AddCommentBottomSheetBinding
import com.divesh.perfex.databinding.EditCommentBottomSheetBinding
import com.divesh.perfex.databinding.FragmentCommentsBinding
import com.divesh.perfex.tasks.domain.adapters.CommentsAdapter
import com.divesh.perfex.tasks.domain.interfaces.CommentsInterface
import com.divesh.perfex.tasks.domain.models.ViewTaskResponseModel
import com.divesh.perfex.tasks.presentation.view.tabs.ViewTaskViewModel
import com.github.dhaval2404.imagepicker.ImagePicker
import com.google.android.material.bottomsheet.BottomSheetDialogFragment
import dagger.hilt.android.AndroidEntryPoint
import okhttp3.MediaType.Companion.toMediaTypeOrNull
import okhttp3.MultipartBody
import okhttp3.RequestBody.Companion.toRequestBody
import java.io.InputStream

@AndroidEntryPoint
class CommentsFragment : Fragment(), CommentsInterface {
    private var _binding: FragmentCommentsBinding? = null
    private val binding get() = _binding!!
    private val listAdapter = CommentsAdapter(arrayListOf(), this)
    private lateinit var lottieAnimation: LottieAnimationView
    private val viewModel: ViewTaskViewModel by viewModels()
    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        _binding = FragmentCommentsBinding.inflate(inflater, container, false)
        val view = binding.root
        observeViewModel()
        initViews()
        return view
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
        binding.addCommentFab.setOnClickListener {
            viewModel.commentAdded.value = null
            val addCommentBottomSheet =
                AddCommentBottomSheet(viewModel, arguments?.getString("id").toString())
            parentFragmentManager.let {
                addCommentBottomSheet.show(it, "AddCommentBottomSheet")
            }
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
        viewModel.commentAdded.observe(viewLifecycleOwner) {
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
                if (it.task.comments != null && it.task.comments.isNotEmpty()) {
                    listAdapter.setRecords(
                        it.task.comments,
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

    override fun onResume() {
        super.onResume()
        viewModel.loadViewTaskInitialData(arguments?.getString("id").toString().toInt())
    }

    override fun editComment(comment: ViewTaskResponseModel.Task.Comment) {
        viewModel.commentAdded.value = null
        val editCommentBottomSheet = EditCommentBottomSheet(viewModel, comment)
        parentFragmentManager.let {
            editCommentBottomSheet.show(it, "EditCommentBottomSheet")
        }
    }

    override fun removeComment(comment: ViewTaskResponseModel.Task.Comment) {
        Extensions().simpleAlert(
            getString(R.string.confirm_deletion),
            requireContext()
        ) { isConfirmed ->
            if (isConfirmed) {
                viewModel.removeComment(comment.id, arguments?.getString("id").toString().toInt())
            }
        }
    }

    class AddCommentBottomSheet(
        private val viewModel: ViewTaskViewModel,
        private val taskId: String
    ) : BottomSheetDialogFragment() {
        private lateinit var _binding: AddCommentBottomSheetBinding
        private val binding get() = _binding
        private var fileName: String? = null
        private var fileInputStream: InputStream? = null
        private var chooseImageLauncher =
            registerForActivityResult(ActivityResultContracts.StartActivityForResult()) { result: ActivityResult ->
                val resultCode = result.resultCode
                val data = result.data
                when (resultCode) {
                    Activity.RESULT_OK -> {
                        if (data != null) {
                            data.data?.let { uri ->
                                if (context != null) {
                                    fileName = Extensions().getFileNameFromPickedImageData(uri, requireContext())
                                    fileInputStream = context?.contentResolver?.openInputStream(uri)
                                    binding.chooseFileBtn.text = fileName
                                }
                            }
                        }
                    }
                    ImagePicker.RESULT_ERROR -> {
                        Toast.makeText(context, ImagePicker.getError(data), Toast.LENGTH_SHORT)
                            .show()
                    }
                    else -> {
                        Toast.makeText(context, "Cancelled", Toast.LENGTH_SHORT).show()
                    }
                }
            }

        override fun onCreateView(
            inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
        ): View {
            _binding = AddCommentBottomSheetBinding.inflate(inflater)
            val view = binding.root
            initViews()
            observeViewModel()
            return view
        }

        private fun initViews() {
            binding.close.setOnClickListener {
                dismiss()
            }
            binding.chooseFileBtn.setOnClickListener {
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
            binding.save.setOnClickListener {
                if (binding.comment.editText?.text.toString().isNotBlank()) {
                    addComment()
                } else {
                    binding.comment.error = getString(R.string.field_required)
                    binding.comment.isErrorEnabled = true
                }
            }
        }

        private fun addComment() {
            var multipartImage: MultipartBody.Part? = null
            if (fileInputStream != null) {
                val requestFile = Extensions().getBytesFromInputStream(fileInputStream)?.toRequestBody()
                if (requestFile != null) {
                    multipartImage = MultipartBody.Part.createFormData(
                        "file[]",
                        fileName,
                        requestFile
                    )
                }
            }
            viewModel.addComment(
                taskId.toRequestBody("multipart/form-data".toMediaTypeOrNull()),
                binding.comment.editText?.text.toString().toRequestBody("multipart/form-data".toMediaTypeOrNull()),
                multipartImage
            )
        }

        private fun observeViewModel() {
            viewModel.commentAdded.observe(viewLifecycleOwner) {
                if (it != null) {
                    if (it) {
                        Extensions().showMessage(binding.root, getString(R.string.comment_added))
                        dismiss()
                    }
                }
            }
        }
    }

    class EditCommentBottomSheet(
        private val viewModel: ViewTaskViewModel,
        private val comment: ViewTaskResponseModel.Task.Comment,
    ) : BottomSheetDialogFragment() {
        private lateinit var _binding: EditCommentBottomSheetBinding
        private val binding get() = _binding
        override fun onCreateView(
            inflater: LayoutInflater, container: ViewGroup?, savedInstanceState: Bundle?
        ): View {
            _binding = EditCommentBottomSheetBinding.inflate(inflater)
            val view = binding.root
            initViews()
            observeViewModel()
            return view
        }

        private fun initViews() {
            binding.comment.editText?.setText(
                comment.content.replace("[task_attachment]", "").parseAsHtml()
            )
            binding.close.setOnClickListener {
                dismiss()
            }
            binding.save.setOnClickListener {
                if (binding.comment.editText?.text.toString().isNotBlank()) {
                    viewModel.updateComment(
                        binding.comment.editText?.text.toString(),
                        comment.id.toString()
                    )
                } else {
                    binding.comment.isErrorEnabled = true
                    binding.comment.error = getString(R.string.field_required)
                }
            }
        }

        private fun observeViewModel() {
            viewModel.commentAdded.observe(viewLifecycleOwner) {
                if (it != null) {
                    if (it) {
                        Extensions().showMessage(binding.root, getString(R.string.comment_added))
                        dismiss()
                    }
                }
            }
        }
    }
}