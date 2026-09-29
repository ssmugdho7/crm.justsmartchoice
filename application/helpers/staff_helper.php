<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @since 2.3.3
 * Get available staff permissions.
 *
 * Modules can use the filter to register additional permissions.
 *
 * @param array $data Additional data passed from role.php and member.php
 *
 * @return array
 */
function get_available_staff_permissions($data = [])
{
    $viewGlobalName = _l('permission_view') . '(' . _l('permission_global') . ')';

    $allPermissionsArray = [
        'view_own' => _l('permission_view_own'),
        'view'     => $viewGlobalName,
        'create'   => _l('permission_create'),
        'edit'     => _l('permission_edit'),
        'delete'   => _l('permission_delete'),
    ];

    $withoutViewOwnPermissionsArray = [
        'view'   => $viewGlobalName,
        'create' => _l('permission_create'),
        'edit'   => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    $withNotApplicableViewOwn = array_merge(
        [
            'view_own' => [
                'not_applicable' => true,
                'name'           => _l('permission_view_own'),
            ],
        ],
        $withoutViewOwnPermissionsArray
    );

    $corePermissions = [
        'bulk_pdf_exporter' => [
            'name'         => _l('bulk_pdf_exporter'),
            'capabilities' => [
                'view' => $viewGlobalName,
            ],
        ],

        'contracts' => [
            'name'         => _l('contracts'),
            'capabilities' => array_merge(
                $allPermissionsArray,
                [
                    'view_all_templates' => _l('permission_view_all_templates'),
                ]
            ),
        ],

        'credit_notes' => [
            'name'         => _l('credit_notes'),
            'capabilities' => $allPermissionsArray,
        ],

        'customers' => [
            'name'         => _l('clients'),
            'capabilities' => $withNotApplicableViewOwn,
            'help'         => [
                'view_own' => _l('permission_customers_based_on_admins'),
            ],
        ],

        'email_templates' => [
            'name'         => _l('email_templates'),
            'capabilities' => [
                'view' => $viewGlobalName,
                'edit' => _l('permission_edit'),
            ],
        ],

        'estimates' => [
            'name'         => _l('estimates'),
            'capabilities' => $allPermissionsArray,
        ],

        'expenses' => [
            'name'         => _l('expenses'),
            'capabilities' => $allPermissionsArray,
        ],

        'invoices' => [
            'name'         => _l('invoices'),
            'capabilities' => $allPermissionsArray,
        ],

        'items' => [
            'name'         => _l('items'),
            'capabilities' => $withoutViewOwnPermissionsArray,
        ],

        'knowledge_base' => [
            'name'         => _l('knowledge_base'),
            'capabilities' => $withoutViewOwnPermissionsArray,
        ],

        'payments' => [
            'name'         => _l('payments'),
            'capabilities' => $withNotApplicableViewOwn,
            'help'         => [
                'view_own' => _l('permission_payments_based_on_invoices'),
            ],
        ],

        'projects' => [
            'name'         => _l('projects'),
            'capabilities' => array_merge(
                $withNotApplicableViewOwn,
                [
                    'create_milestones' => _l('permission_create_timesheets'),
                    'edit_milestones'   => _l('permission_edit_milestones'),
                    'delete_milestones' => _l('permission_delete_milestones'),
                ]
            ),
            'help' => [
                'view'     => _l('help_project_permissions'),
                'view_own' => _l('permission_projects_based_on_assignee'),
            ],
        ],

        'proposals' => [
            'name'         => _l('proposals'),
            'capabilities' => array_merge(
                $allPermissionsArray,
                [
                    'view_all_templates' => _l('permission_view_all_templates'),
                ]
            ),
        ],

        'reports' => [
            'name'         => _l('reports'),
            'capabilities' => [
                'view'            => $viewGlobalName,
                'view-timesheets' => _l('permission_view_timesheet_report'),
            ],
        ],

        'roles' => [
            'name'         => _l('roles'),
            'capabilities' => $withoutViewOwnPermissionsArray,
        ],

        'settings' => [
            'name'         => _l('settings'),
            'capabilities' => [
                'view' => $viewGlobalName,
                'edit' => _l('permission_edit'),
            ],
        ],

        'staff' => [
            'name'         => _l('staff'),
            'capabilities' => $withoutViewOwnPermissionsArray,
        ],

        'subscriptions' => [
            'name'         => _l('subscriptions'),
            'capabilities' => $allPermissionsArray,
        ],

        'tasks' => [
            'name'         => _l('tasks'),
            'capabilities' => array_merge(
                $withNotApplicableViewOwn,
                [
                    'edit_timesheet'       => _l('permission_edit_timesheets'),
                    'edit_own_timesheet'   => _l('permission_edit_own_timesheets'),
                    'delete_timesheet'     => _l('permission_delete_timesheets'),
                    'delete_own_timesheet' => _l('permission_delete_own_timesheets'),
                ]
            ),
            'help' => [
                'view'     => _l('help_tasks_permissions'),
                'view_own' => _l('permission_tasks_based_on_assignee'),
            ],
        ],

        'checklist_templates' => [
            'name'         => _l('checklist_templates'),
            'capabilities' => [
                'create' => _l('permission_create'),
                'delete' => _l('permission_delete'),
            ],
        ],

        'estimate_request' => [
            'name'         => _l('estimate_request'),
            'capabilities' => $allPermissionsArray,
        ],
    ];

    $addLeadsPermission = true;

    if (isset($data['staff_id']) && $data['staff_id']) {
        if (!is_staff_member($data['staff_id'])) {
            $addLeadsPermission = false;
        }
    }

    if ($addLeadsPermission) {
        $corePermissions['leads'] = [
            'name'         => _l('leads'),
            'capabilities' => [
                'view'   => $viewGlobalName,
                'delete' => _l('permission_delete'),
            ],
            'help' => [
                'view' => _l('help_leads_permission_view'),
            ],
        ];
    }

    return hooks()->apply_filters(
        'staff_permissions',
        $corePermissions,
        $data
    );
}

/**
 * Get staff by ID or the currently logged-in staff member.
 *
 * @param mixed $id Staff ID
 *
 * @return mixed
 */
function get_staff($id = null)
{
    if (empty($id) && isset($GLOBALS['current_user'])) {
        return $GLOBALS['current_user'];
    }

    if (empty($id)) {
        return null;
    }

    if (!class_exists('staff_model', false)) {
        get_instance()->load->model('staff_model');
    }

    return get_instance()->staff_model->get($id);
}

/**
 * Build a list of possible staff profile-picture filenames.
 *
 * The database stores the employee's real filename in:
 *
 * tblstaff.profile_image
 *
 * The file can exist as:
 *
 * filename.jpg
 * small_filename.jpg
 * thumb_filename.jpg
 * thumbnail_filename.jpg
 *
 * Some existing database records may already contain a prefix, such as:
 *
 * thumb_shivam.jpg
 *
 * This function prevents incorrect names such as:
 *
 * thumb_thumb_shivam.jpg
 * small_thumb_shivam.jpg
 *
 * @param string $filename Stored database filename
 * @param string $type     Requested image size
 *
 * @return array
 */
function smart_choice_staff_image_candidates($filename, $type = 'small')
{
    $filename = trim((string) $filename);
    $filename = basename(str_replace('\\', '/', $filename));

    if ($filename === '') {
        return [];
    }

    /*
     * Always check the exact filename from the database first.
     *
     * Examples:
     * Harold Cabrera.jpg
     * ChatGPT Image Nov 13, 2025, 12_00_50 PM.png
     * thumb_shivam.jpg
     */
    $candidates = [
        $filename,
    ];

    $type = trim((string) $type);

    if ($type !== '') {
        $typePrefix = rtrim($type, '_') . '_';

        if (strpos($filename, $typePrefix) !== 0) {
            $candidates[] = $typePrefix . $filename;
        }
    }

    if (strpos($filename, 'small_') !== 0) {
        $candidates[] = 'small_' . $filename;
    }

    if (strpos($filename, 'thumb_') !== 0) {
        $candidates[] = 'thumb_' . $filename;
    }

    if (strpos($filename, 'thumbnail_') !== 0) {
        $candidates[] = 'thumbnail_' . $filename;
    }

    /*
     * When a database filename is already prefixed, also test the
     * unprefixed original filename.
     *
     * Example:
     * thumb_shivam.jpg
     *
     * Additional candidate:
     * shivam.jpg
     */
    $knownPrefixes = [
        'small_',
        'thumb_',
        'thumbnail_',
    ];

    foreach ($knownPrefixes as $prefix) {
        if (strpos($filename, $prefix) === 0) {
            $withoutPrefix = substr($filename, strlen($prefix));

            if ($withoutPrefix !== '') {
                $candidates[] = $withoutPrefix;

                if (strpos($withoutPrefix, 'small_') !== 0) {
                    $candidates[] = 'small_' . $withoutPrefix;
                }

                if (strpos($withoutPrefix, 'thumb_') !== 0) {
                    $candidates[] = 'thumb_' . $withoutPrefix;
                }

                if (strpos($withoutPrefix, 'thumbnail_') !== 0) {
                    $candidates[] = 'thumbnail_' . $withoutPrefix;
                }
            }
        }
    }

    return array_values(array_unique($candidates));
}

/**
 * Resolve the physical and public path of a staff profile picture.
 *
 * @param mixed  $staffId Staff ID
 * @param string $filename Stored profile filename
 * @param string $type Requested image size
 *
 * @return array|null
 */
function smart_choice_resolve_staff_profile_image(
    $staffId,
    $filename,
    $type = 'small'
) {
    $staffId = (int) $staffId;

    if ($staffId <= 0) {
        return null;
    }

    $filename = trim((string) $filename);

    if ($filename === '') {
        return null;
    }

    $relativeDirectory = 'uploads/staff_profile_images/'
        . $staffId
        . '/';

    $absoluteDirectory = rtrim(FCPATH, '/\\')
        . DIRECTORY_SEPARATOR
        . 'uploads'
        . DIRECTORY_SEPARATOR
        . 'staff_profile_images'
        . DIRECTORY_SEPARATOR
        . $staffId
        . DIRECTORY_SEPARATOR;

    $candidates = smart_choice_staff_image_candidates(
        $filename,
        $type
    );

    foreach ($candidates as $candidate) {
        $absolutePath = $absoluteDirectory . $candidate;

        if (is_file($absolutePath) && is_readable($absolutePath)) {
            return [
                'filename'      => $candidate,
                'absolute_path' => $absolutePath,
                'relative_path' => $relativeDirectory . $candidate,
                'url'           => base_url(
                    $relativeDirectory
                    . rawurlencode($candidate)
                ),
            ];
        }
    }

    return null;
}

/**
 * Return staff profile image URL.
 *
 * This function supports the exact SQL results stored in:
 *
 * tblstaff.profile_image
 *
 * Examples:
 *
 * Harold Cabrera.jpg
 * daniel jordan.jpg
 * bob pitcman.png
 * ChatGPT Image Nov 13, 2025, 12_00_50 PM.png
 * MARIA.png
 * thumb_shivam.jpg
 *
 * @param mixed  $staff_id Staff ID
 * @param string $type     Requested image type
 *
 * @return string
 */
function staff_profile_image_url($staff_id, $type = 'small')
{
    $placeholder = base_url(
        'assets/images/user-placeholder.jpg'
    );

    $staffId = (int) $staff_id;

    if ($staffId <= 0) {
        return $placeholder;
    }

    $staff = null;

    if (
        (string) $staffId === (string) get_staff_user_id()
        && isset($GLOBALS['current_user'])
    ) {
        $staff = $GLOBALS['current_user'];
    } else {
        $CI = &get_instance();

        $staff = $CI->db
            ->select('profile_image')
            ->where('staffid', $staffId)
            ->get(db_prefix() . 'staff')
            ->row();
    }

    if (!$staff || empty($staff->profile_image)) {
        return $placeholder;
    }

    $resolved = smart_choice_resolve_staff_profile_image(
        $staffId,
        $staff->profile_image,
        $type
    );

    if (!$resolved) {
        return $placeholder;
    }

    return $resolved['url'];
}

/**
 * Return staff profile image HTML.
 *
 * This function is used by Perfex CRM and modules for employee images.
 *
 * @param mixed  $id        Staff ID
 * @param array  $classes   CSS classes
 * @param string $type      Requested image type
 * @param array  $img_attrs Additional HTML image attributes
 *
 * @return string
 */
function staff_profile_image(
    $id,
    $classes = ['staff-profile-image'],
    $type = 'small',
    $img_attrs = []
) {
    $staffId = (int) trim((string) $id);

    $imageUrl = staff_profile_image_url(
        $staffId,
        $type
    );

    $attributes = '';

    foreach ((array) $img_attrs as $key => $value) {
        $key = trim((string) $key);

        if ($key === '') {
            continue;
        }

        $attributes .= ' '
            . html_escape($key)
            . '="'
            . html_escape((string) $value)
            . '"';
    }

    $classList = [];

    foreach ((array) $classes as $className) {
        $className = trim((string) $className);

        if ($className !== '') {
            $classList[] = $className;
        }
    }

    if (empty($classList)) {
        $classList[] = 'staff-profile-image';
    }

    $classList = array_values(
        array_unique($classList)
    );

    $altText = 'Staff profile image';

    if ($staffId > 0) {
        $staff = null;

        if (
            (string) $staffId === (string) get_staff_user_id()
            && isset($GLOBALS['current_user'])
        ) {
            $staff = $GLOBALS['current_user'];
        } else {
            $CI = &get_instance();

            $cacheKey = 'staff-profile-image-data-' . $staffId;

            $staff = $CI->app_object_cache->get(
                $cacheKey
            );

            if (!$staff) {
                $staff = $CI->db
                    ->select(
                        'profile_image,firstname,lastname'
                    )
                    ->where('staffid', $staffId)
                    ->get(db_prefix() . 'staff')
                    ->row();

                if ($staff) {
                    $CI->app_object_cache->add(
                        $cacheKey,
                        $staff
                    );
                }
            }
        }

        if ($staff) {
            $fullName = trim(
                (string) $staff->firstname
                . ' '
                . (string) $staff->lastname
            );

            if ($fullName !== '') {
                $altText = $fullName;
            }
        }
    }

    return '<img'
        . $attributes
        . ' src="'
        . html_escape($imageUrl)
        . '" class="'
        . html_escape(implode(' ', $classList))
        . '" alt="'
        . html_escape($altText)
        . '">';
}

/**
 * Get staff full name.
 *
 * @param string $userid Optional staff ID
 *
 * @return string
 */
function get_staff_full_name($userid = '')
{
    $tmpStaffUserId = get_staff_user_id();

    if ($userid == '' || $userid == $tmpStaffUserId) {
        if (isset($GLOBALS['current_user'])) {
            return $GLOBALS['current_user']->firstname
                . ' '
                . $GLOBALS['current_user']->lastname;
        }

        $userid = $tmpStaffUserId;
    }

    $CI = &get_instance();

    $staff = $CI->app_object_cache->get(
        'staff-full-name-data-' . $userid
    );

    if (!$staff) {
        $CI->db->where('staffid', $userid);

        $staff = $CI->db
            ->select('firstname,lastname')
            ->from(db_prefix() . 'staff')
            ->get()
            ->row();

        $CI->app_object_cache->add(
            'staff-full-name-data-' . $userid,
            $staff
        );
    }

    return $staff
        ? $staff->firstname . ' ' . $staff->lastname
        : '';
}

/**
 * Get staff default language.
 *
 * @param mixed $staffid Staff ID
 *
 * @return mixed
 */
function get_staff_default_language($staffid = '')
{
    if (!is_numeric($staffid)) {
        if (isset($GLOBALS['current_user'])) {
            return $GLOBALS['current_user']->default_language;
        }

        $staffid = get_staff_user_id();
    }

    $CI = &get_instance();

    $CI->db->select('default_language');
    $CI->db->from(db_prefix() . 'staff');
    $CI->db->where('staffid', $staffid);

    $staff = $CI->db->get()->row();

    if ($staff) {
        return $staff->default_language;
    }

    return '';
}

/**
 * Get staff recent search history.
 *
 * @param mixed $staff_id Staff ID
 *
 * @return array|mixed
 */
function get_staff_recent_search_history($staff_id = null)
{
    $recentSearches = get_staff_meta(
        $staff_id ? $staff_id : get_staff_user_id(),
        'recent_searches'
    );

    if ($recentSearches == '') {
        $recentSearches = [];
    } else {
        $recentSearches = json_decode($recentSearches);
    }

    return $recentSearches;
}

/**
 * Update staff recent search history.
 *
 * @param array $history Search history
 * @param mixed $staff_id Staff ID
 *
 * @return array
 */
function update_staff_recent_search_history(
    $history,
    $staff_id = null
) {
    $totalRecentSearches = hooks()->apply_filters(
        'total_recent_searches',
        5
    );

    $history = array_reverse($history);
    $history = array_unique($history);
    $history = array_splice(
        $history,
        0,
        $totalRecentSearches
    );

    update_staff_meta(
        $staff_id ? $staff_id : get_staff_user_id(),
        'recent_searches',
        json_encode($history)
    );

    return $history;
}

/**
 * Check whether the user is a staff member.
 *
 * The staff profile includes an option to mark someone as not being a
 * staff member, such as a contractor. Some features are disabled for
 * those users.
 *
 * @param string $staff_id Staff ID
 *
 * @return bool
 */
function is_staff_member($staff_id = '')
{
    $CI = &get_instance();

    if ($staff_id == '') {
        if (isset($GLOBALS['current_user'])) {
            return $GLOBALS['current_user']->is_not_staff === '0';
        }

        $staff_id = get_staff_user_id();
    }

    $CI->db
        ->where('staffid', $staff_id)
        ->where('is_not_staff', 0);

    return $CI->db->count_all_results(
        db_prefix() . 'staff'
    ) > 0;
}