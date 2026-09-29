package com.divesh.perfex.sales.domain.models.proposals


import kotlinx.parcelize.Parcelize
import android.os.Parcelable

@Parcelize
data class ProposalCommentsResponseModel(
    var comments: List<Comment> = listOf(),
    var message: String? = "", // Proposals Comments loaded
    var status: Int = 0 // 1
) : Parcelable {
    @Parcelize
    data class Comment(
        var addedDate: String? = "", // 2023-01-02 20:41:01
        var content: String? = "", // Hi this is a first content<br />and this is line 2 on the 1st comment.
        var dateadded: String? = "", // 2 days ago
        var id: Int = 0, // 1
        var proposalid: Int = 0, // 1
        var staffImage: String? = "", // http://perfex.test/assets/images/user-placeholder.jpg
        var staffName: String? = "", // Divesh Ahuja
        var staffid: Int = 0 // 1
    ) : Parcelable
}