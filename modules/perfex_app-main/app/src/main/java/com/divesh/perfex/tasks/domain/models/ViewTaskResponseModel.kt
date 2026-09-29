package com.divesh.perfex.tasks.domain.models

data class ViewTaskResponseModel(
    val message: String = "", // Task loaded
    val staff: List<Staff>? = listOf(),
    val status: Int = 0, // 1
    val task: Task = Task(),
    val task_checklist_templates: List<Any> = listOf(),
    val task_reminders: List<TaskReminder> = listOf()
) {
    data class Staff(
        val active: Int = 0, // 1
        val admin: Int = 0, // 1
        val datecreated: String = "", // 2023-01-24 21:45:44
        val default_language: String? = "", // null
        val direction: String? = "", // null
        val email: String = "", // diveshahuja8@gmail.com
        val email_signature: String? = "", // null
        val facebook: String? = "", // null
        val firstname: String = "", // Divesh
        val google_auth_secret: String? = "", // null
        val hourly_rate: Int = 0, // 0
        val is_not_staff: Int = 0, // 0
        val last_activity: String = "", // 2023-02-11 15:20:59
        val last_ip: String = "", // 127.0.0.1
        val last_login: String = "", // 2023-02-11 09:08:25
        val last_password_change: String? = "", // null
        val lastname: String = "", // Ahuja
        val linkedin: String? = "", // null
        val media_path_slug: String? = "", // null
        val new_pass_key: String? = "", // null
        val new_pass_key_requested: String? = "", // null
        val password: String = "", // $2a$08$HH1FTfIJ55KVOZCiNL4CO.y7Hq.JhN6FlbOq7SHJOiVeY4MBu2DHi
        val phonenumber: String? = "", // null
        val profile_image: String? = "", // null
        val role: String? = "", // null
        val skype: String? = "", // null
        val staffid: Int = 0, // 1
        val two_factor_auth_code: String? = "", // null
        val two_factor_auth_code_requested: String? = "", // null
        val two_factor_auth_enabled: Int = 0 // 0
    )

    data class Task(
        val addedfrom: Int = 0, // 1
        val assignees: List<Assignee>? = listOf(),
        val assignees_ids: List<Int>? = listOf(),
        val attachments: List<Attachment>? = listOf(),
        val billable: Int = 0, // 1
        val billed: Int = 0, // 0
        val checklist_items: List<ChecklistItems>? = listOf(),
        val comments: List<Comment>? = listOf(),
        val current_user_is_assigned: Boolean = false, // true
        val current_user_is_creator: Boolean = false, // true
        val custom_recurring: Int = 0, // 0
        val cycles: Int = 0, // 0
        val dateadded: String = "", // 2022-12-20 02:03:43
        val datefinished: String? = "", // null
        val deadline_notified: Int = 0, // 0
        val description: String = "", // <p><span class="text-muted">No description for this task</span></p><p><span class="text-muted">This is a demo sessfnds fksdjf&#160;</span></p>
        val duedate: String = "", // 2022-11-22
        val followers: List<Follower>? = listOf(),
        val followers_ids: List<Int>? = listOf(),
        val hourly_rate: Int = 0, // 2
        val id: Int = 0, // 8
        val invoice_id: Int = 0, // 0
        val is_added_from_contact: Int = 0, // 0
        val is_public: Int = 0, // 0
        val is_recurring_from: String? = "", // null
        val kanban_order: Int = 0, // 1
        val last_recurring_date: String? = "", // null
        val milestone: Int = 0, // 0
        val milestone_name: String = "",
        val milestone_order: Int = 0, // 0
        val name: String = "", // gbb jg b
        val priority: Int = 0, // 4
        val recurring: Int = 0, // 0
        val recurring_type: String? = "", // null
        val rel_id: Int = 0, // 8
        val rel_type: String = "", // customer
        val repeat_every: Int = 0, // 0
        val startdate: String = "", // 2022-11-21
        val status: Int = 0, // 4
        val timesheets: List<Any>? = listOf(),
        val total_cycles: Int = 0, // 0
        val visible_to_client: Int = 0 // 0
    ) {
        data class Assignee(
            val assigned_from: Int = 0, // 1
            val assigneeid: Int = 0, // 2
            val firstname: String = "", // Demo 
            val full_name: String = "", // Demo  Staff
            val id: Int = 0, // 3
            val is_assigned_from_contact: Int = 0, // 0
            val lastname: String = "" // Staff
        )

        data class Attachment(
            val attachment_key: String = "", // 9895309e0ac379dcb60642250ddc364c
            val comment_file_id: Int? = 0, // 3
            val contact_id: Int = 0, // 0
            val dateadded: String = "", // 2023-02-11 13:12:54
            val external: String? = "", // null
            val external_link: String? = "", // null
            val file_name: String = "", // Screenshot from 2023-01-26 16-18-05-1.png
            val filetype: String = "", // image/png
            val id: Int = 0, // 2
            val rel_id: Int = 0, // 8
            val rel_type: String = "", // task
            val staffid: Int = 0, // 1
            val task_comment_id: Int = 0, // 0
            val thumbnail_link: String? = "", // null
            val visible_to_customer: Int = 0 // 0
        )

        data class ChecklistItems(
            val addedfrom: Int = 0, // 1
            val assigned: String? = "", // null
            val dateadded: String = "", // 2023-02-11 13:12:02
            val description: String = "", // Task list 2
            val finished: Int = 0, // 1
            val finished_from: Int = 0, // 1
            val id: Int = 0, // 2
            val list_order: Int = 0, // 2
            val taskid: Int = 0 // 8
        )

        data class Comment(
            val attachments: List<Attachment>? = listOf(),
            val contact_id: Int = 0, // 0
            val content: String = "", // [task_attachment]
            val dateadded: String = "", // 2023-02-11 13:12:54
            val file_id: Int = 0, // 2
            val firstname: String = "", // Divesh
            val id: Int = 0, // 3
            val lastname: String = "", // Ahuja
            val staff_full_name: String = "", // Divesh Ahuja
            val staffid: Int = 0 // 1
        ) {
            data class Attachment(
                val attachment_key: String = "", // 6a9ea91f87c000e689940e90c6052685
                val comment_file_id: String? = "", // null
                val contact_id: Int = 0, // 0
                val dateadded: String = "", // 2023-02-11 13:12:44
                val `external`: String? = "", // null
                val external_link: String? = "", // null
                val file_name: String = "", // Screenshot from 2023-01-26 16-18-05.png
                val filetype: String = "", // image/png
                val id: Int = 0, // 1
                val rel_id: Int = 0, // 8
                val rel_type: String = "", // task
                val staffid: Int = 0, // 1
                val task_comment_id: Int = 0, // 2
                val thumbnail_link: String? = "", // null
                val visible_to_customer: Int = 0 // 0
            )
        }

        data class Follower(
            val followerid: Int = 0, // 2
            val full_name: String = "", // Demo  Staff
            val id: Int = 0 // 1
        )
    }

    data class TaskReminder(
        val creator: Int = 0, // 1
        val date: String = "", // 2023-02-11 13:16:00
        val description: String = "", // Demo description for reminder
        val id: Int = 0, // 1
        val isnotified: Int = 0, // 0
        val notify_by_email: Int = 0, // 0
        val rel_id: Int = 0, // 8
        val rel_type: String = "", // task
        val staff: Int = 0 // 1
    )
}