<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/css/chat.css') . '?v=' . $module_version; ?>">
<link rel="stylesheet" href="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/css/whatsbot_tailwind.css') . '?v=' . $module_version; ?>">

<?php init_head(); ?>
<?php
if (empty(get_option('pusher_app_key')) || empty(get_option('pusher_app_secret')) || empty(get_option('pusher_app_id')) || empty(get_option('pusher_cluster'))) { ?>
    <div id="wrapper" class="tw-h-full tw-flex tw-items-center tw-justify-center">
        <div class="tw-container">
            <div class="tw-flex tw-justify-center">
                <div class="tw-w-full tw-max-w-md">
                    <div class="tw-bg-white tw-shadow-md tw-rounded-lg tw-p-6">
                        <div class="tw-text-center tw-mb-4">
                            <h3 class="tw-text-xl tw-font-semibold">Pusher Account Setup</h3>
                        </div>
                        <div class="tw-text-center">
                            <h4 class="tw-text-lg tw-mb-4">It seems that your Pusher account is not configured correctly.</h4>
                            <p class="tw-mb-4">Please configure your Pusher account:</p>
                            <a href="<?= admin_url('settings?group=pusher'); ?>" class="tw-bg-blue-500 tw-py-2 tw-px-4 tw-rounded tw-w-full tw-block tw-mb-4">Perfex CRM Settings → Pusher.com</a>
                            <p class="tw-mb-4">For guidance, you can follow this tutorial:</p>
                            <a href="https://help.perfexcrm.com/setup-realtime-notifications-with-pusher-com/" target="_blank" class="tw-text-blue-500">See how to set up Pusher from Perfex CRM documentation</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <?php init_tail(); ?>
<?php exit;
}
?>
<?php
$csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

?>
<div id="wrapper">
    <div id="app" class="p-2" :class="{ dark: darkMode }" v-cloak>
        <div v-if="errorMessage" class="bg-red-200 border border-red-600 text-red-800 p-2 rounded-md  w-full flex justify-between items-center" v-cloak>
            <p class="text-sm">{{ errorMessage }}</p>
            <button class="hideMessage text-red-600 hover:text-red-800 focus:outline-none">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div v-if="sameNumErrorMessage" class="bg-red-200 border border-red-600 text-red-800 w-full p-2 rounded-md flex justify-between items-center" v-cloak>
            <p class="text-sm">{{ sameNumErrorMessage }}</p>
            <button class="hideMessage text-red-600 hover:text-red-800 focus:outline-none">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div>

            <div class="flex gap-2 relative sm:h-[calc(100vh_-_90px)] h-full sm:min-h-0 " :class="{ 'min-h-[999px]': isShowChatMenu }" v-cloak>
                <!-- Sidebar Start -->
                <div
                    class="panel dark:bg-[#1E293B] p-2 flex-none max-w-sm w-full absolute xl:relative z-10 space-y-4 h-full overflow-hidden mainsidebar-class" :class="isShowChatMenu && '!block !overflow-y-auto'" v-cloak>
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="flex-none">
                                <img class="rounded-full h-12 w-12 object-cover" src="<?= !empty(get_option("wac_profile_picture_url")) ? get_option("wac_profile_picture_url") : base_url('assets/images/user-placeholder.jpg'); ?>" alt="profile">
                            </div>
                            <div class="mx-3" v-if="wb_selectedinteraction && typeof wb_selectedinteraction === 'object'">
                                <span class="text-md dark:text-gray-200"><?php echo _l('from'); ?> {{ wb_selectedinteraction.wa_no }}</span>
                            </div>
                        </div>

                        <div class="flex justify-end items-center ml-auto cursor-pointer gap-4">
                            <button
                                type="button"
                                class="xl:hidden"
                                v-on:click="isShowChatMenu = !isShowChatMenu">
                                <i class="fa-regular fa-xl fa-xmark fa-xmark-circle"></i>
                            </button>

                            <img class="rounded-full w-4 h-4" v-if="!darkMode" v-on:click="toggleDarkMode" src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/images/moon.png'); ?>" alt="moon">
                            <img class="rounded-full w-6 h-6" v-if="darkMode" v-on:click="toggleDarkMode" src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/images/sun.png'); ?>" alt="sun">
                        </div>
                    </div>

                    <div class="flex items-center" v-cloak>
                        <div class="flex justify-between items-center w-full">
                            <select v-model="wb_selectedWaNo" v-on:change="wb_filterInteractions" id="wb_selectedWaNo" class="w-full rounded-md border-0 p-2 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-inset dark:bg-gray-800 dark:text-gray-200 focus:ring-blue-500 sm:text-sm sm:leading-6 mr-2">
                                <option v-for="(interaction, index) in wb_uniqueWaNos" :key="index" :value="interaction.wa_no" class="bg-[#F0F2F5] dark:bg-gray-700 dark:text-gray-200" :selected="wb_selectedWaNo === 'interaction.wa_no'">
                                    {{ interaction.wa_no }}
                                </option>
                                <option value="*"><?php echo _l('all_chat'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="flex items-center" v-cloak>
                        <div class="flex justify-around items-center w-full gap-2">
                            <button
                                class="tab-button px-2 py-2 rounded-md text-xs font-medium transition-colors duration-200 ease-in-out w-full"
                                :class="selectedTab === 'searching' ? 'bg-blue-500 text-white shadow-lg' : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'"
                                v-on:click="resetFilters">
                                <i class="fa fa-search dark:text-gray-200"></i> <?php echo _l('searching'); ?>
                            </button>

                            <button
                                class="tab-button px-4 py-2 rounded-md text-xs font-medium transition-colors duration-200 ease-in-out w-full"
                                :class="selectedTab === 'filters' ? 'bg-blue-500 text-white shadow-lg' : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600'"
                                v-on:click="selectedTab = 'filters'">
                                <i class="fa fa-filter dark:text-gray-200"></i> <?php echo _l('filters'); ?>
                            </button>
                        </div>
                    </div>
                    <div v-if="selectedTab === 'searching'" v-cloak>
                        <div class="flex justify-between items-center w-full ">
                            <input id="wb_searchText" type="text" name="wb_searchText" class="form-input outline-none rounded-md p-2 w-full  dark:bg-[#1E293B] dark:text-gray-200 dark:placeholder-gray-400 placeholder-gray-600" placeholder="Searching..." v-model="wb_searchText" />
                            <div class="relative">
                                <div class="absolute right-[30px] top-1/2 -translate-y-1/2 ">
                                    <i class="fa fa-search dark:text-gray-200"></i>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- Tab content for filters -->
                    <div v-if="selectedTab === 'filters'" v-cloak>
                        <div class="flex items-center">
                            <div class="flex justify-between flex-col items-center w-full gap-2">
                                <div class="flex justify-between items-center gap-2 w-full">
                                    <!-- Relation Type Dropdown -->
                                    <div class="w-full" v-cloak>
                                        <label for="relationType" class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                            <?php echo _l('relation_type'); ?>
                                        </label>
                                        <select id="relationType" v-on:change="wb_handleAllFilters" v-model="wb_reltype_filter" class="w-full rounded-md border-0 p-1 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-blue-500 dark:bg-[#1E293B] dark:text-gray-200 sm:text-sm">
                                            <option value=""><?php echo _l('select_type'); ?></option>
                                            <option v-for="relType in wb_requireData?.rel_types" :key="relType.key" :value="relType.key" class="text-gray-900 dark:text-gray-200">
                                                {{ relType.value }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="w-full" v-cloak>
                                        <label for="agents" class="block text-xs font-medium text-gray-700 dark:text-gray-200">
                                            <?php echo _l('agents'); ?>
                                        </label>
                                        <select id="agents" v-on:change="wb_handleAllFilters" v-model="wb_agents_filter" class="w-full rounded-md border-0 p-1 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-blue-500 dark:bg-[#1E293B] dark:text-gray-200 sm:text-sm">
                                            <option value=""><?php echo _l('select_agents'); ?></option>
                                            <option v-for="agent in wb_requireData?.agents" :key="agent.staffid" :value="agent.staffid" class="text-gray-900 dark:text-gray-200">
                                                {{ agent.firstname + ' ' + agent.lastname }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center gap-2 w-full" v-cloak>
                                    <!-- Groups Dropdown -->
                                    <div class="w-full" v-if="wb_reltype_filter === 'contacts'">
                                        <label for="groups" class="block text-xs font-medium text-gray-700 dark:text-gray-200"> <?php echo _l('groups'); ?></label>
                                        <select id="groups" v-on:change="wb_handleAllFilters" v-model="wb_contact_groups" class="w-full rounded-md border-0 p-1 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-blue-500 dark:bg-[#1E293B] dark:text-gray-200 sm:text-sm">
                                            <option value=""><?php echo _l('select_group'); ?></option>
                                            <option v-for="groups in wb_requireData?.contact_groups" :key="groups.id" :value="groups.id" class="text-gray-900 dark:text-gray-200">
                                                {{ groups.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <!-- Source Dropdown -->
                                    <div class="w-full" v-if="wb_reltype_filter === 'leads'" v-cloak>
                                        <label for="source" class="block text-xs font-medium text-gray-700 dark:text-gray-200"> <?php echo _l('source'); ?></label>
                                        <select id="source" v-on:change="wb_handleAllFilters" v-model="wb_lead_sources" class="w-full rounded-md border-0 p-1 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-blue-500 dark:bg-[#1E293B] dark:text-gray-200 sm:text-sm">
                                            <option value=""><?php echo _l('select_source'); ?></option>
                                            <option v-for="source in wb_requireData?.lead_sources" :key="source.id" :value="source.id" class="text-gray-900 dark:text-gray-200">
                                                {{ source.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <!-- Status Dropdown -->
                                    <div class="w-full" v-if="wb_reltype_filter === 'leads'" v-cloak>
                                        <label for="status" class="block text-xs font-medium text-gray-700 dark:text-gray-200"> <?php echo _l('status'); ?></label>
                                        <select id="status" v-on:change="wb_handleAllFilters" v-model="wb_lead_status" class="w-full rounded-md border-0 p-1 text-gray-900 shadow-sm ring-1 ring-inset focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-200 sm:text-sm">
                                            <option value=""><?php echo _l('select_status'); ?></option>
                                            <option v-for="status in wb_requireData?.lead_status" :key="status.id" :value="status.id" class="text-gray-900 dark:text-gray-200">
                                                {{ status.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="h-px w-full custom-border" v-cloak></div>
                    <div v-if="noResultsMessage" v-html="noResultsMessage" class="text-red-500" v-cloak>
                    </div>
                    <div :class="selectedTab === 'filters' ? 'h-full min-h-[100px] sm:h-[calc(100vh_-_358px)] overflow-y-auto' : 'h-full min-h-[100px] sm:h-[calc(100vh_-_340px)] overflow-y-auto'" v-cloak>
                        <div class=" hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer custom-border rounded" v-for="(interaction, index) in wb_displayedInteractions" :key="interaction.id" v-on:click="wb_selectinteraction(interaction.id)" :class="{'bg-gray-200': wb_selectedinteraction && wb_selectedinteraction.id === interaction.id,'dark:bg-gray-700': wb_selectedinteraction && wb_selectedinteraction.id === interaction.id}">
                            <div class="flex items-center gap-2 w-full">
                                <div class="p-2 flex items-center justify-center">
                                    <p :title="interaction.receiver_id"
                                        data-toggle="tooltip"
                                        data-placement="top" class="rounded-full bg-green-400 w-12 h-12 flex items-center justify-center text-center font-semibold text-gray-700">
                                        {{ wb_getAvatarInitials(interaction.name) }}
                                    </p>
                                </div>
                                <div class="side-chat flex justify-between w-[75%] ">
                                    <div class="chat-name flex flex-col">
                                        <div class="flex justify-1 items-center gap-2">
                                            <h5 :title="interaction.receiver_id"
                                                data-toggle="tooltip"
                                                data-placement="top"
                                                class="text-md text-gray-700 font-sans font-semibold dark:text-gray-200">{{ interaction.name }}</h5>
                                            <p class="text-md text-gray-500 font-sans font-normal flex items-center mb-1">
                                                <span
                                                    :class="{
                                                        'bg-violet-100 text-purple-800': interaction.type === 'leads',
                                                        'bg-red-100 text-red-800': interaction.type === 'contacts',
                                                    }"
                                                    class="inline-block mt-1 text-xs font-semibold px-2 rounded ">
                                                    {{ interaction.type }}
                                                </span>
                                            </p>
                                        </div>
                                        <span v-html="wb_truncateText(interaction.last_message, 30)" class="dark:text-gray-200"></span>
                                    </div>
                                    <div class="flex flex-col gap-2 items-end">
                                        <p class="text-[0.80rem] font-sans text-gray-500 font-normal dark:text-gray-400">{{ wb_formatTime(interaction.time_sent)
											}}</p>
                                        <div class="flex gap-4 justify-center items-center">
                                            <span v-on:click="wb_deleteInteraction(interaction.id)" class="dele-icn"><i class="fa-solid text-red-500 float-right fa-trash" data-toggle="tooltip" data-placement="top" title="<?php echo _l('remove_chat'); ?>"></i>
                                            </span>
                                            <span v-if="wb_countUnreadMessages(interaction.id) > 0" class="bg-green-500 text-white text-xs font-semibold py-1 px-2 rounded-full">
                                                {{ wb_countUnreadMessages(interaction.id) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sidebar end -->
                <!-- Main content start-->
                <div class="panel flex-1 h-full dark:bg-[#1E293B]" v-cloak>
                    <template v-if="!isShowUserChat">
                        <div class="flex items-center justify-center h-full relative p-4">
                            <button
                                type="button"
                                class="xl:hidden absolute top-4 ltr:left-4 rtl:right-4 hover:text-primary dark:text-gray-200"
                                v-on:click="isShowChatMenu = !isShowChatMenu">
                                <i class="fa fa-align-left fa-xl"></i>
                            </button>
                            <div class="py-8 flex items-center justify-center flex-col">
                                <div class="w-[280px] md:w-[430px] mb-8 h-[calc(100vh_-_320px)] min-h-[120px] text-white dark:text-[#0e1726]">
                                    <img class=" w-full h-full" v-if="!darkMode" src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/images/light_mob.svg'); ?>" alt="light">
                                    <img class=" w-full h-full" v-if="darkMode" src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/images/dark_mob.svg'); ?>" alt="dark">
                                </div>
                                <div class="flex justify-center item-center gap-4 p-2 font-semibold rounded-md max-w-[190px] mx-auto dark:text-gray-400">
                                    <span><?= _l('click_user_to_chat'); ?></span>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template>
                        <div v-if="wb_selectedinteraction && typeof wb_selectedinteraction === 'object'" class="relative h-full " v-cloak>
                            <div class="flex justify-between items-center p-2 bg-white dark:bg-[#1E293B] rounded relative gap-2">
                                <!-- 1st -->
                                <div class="flex justify-between items-center space-x-4">
                                    <button
                                        type="button"
                                        class="xl:hidden dark:text-gray-200"
                                        v-on:click="isShowChatMenu = !isShowChatMenu">
                                        <i class="fa fa-align-left fa-xl"></i>
                                    </button>
                                    <span class="rounded-full w-12 h-12 flex items-center justify-center text-center font-semibold text-gray-700 bg-green-400">
                                        {{wb_getAvatarInitials(wb_selectedinteraction.name) }}
                                    </span>
                                    <div class="flex flex-col">
                                        <div class="flex justify-start items-center gap-4">
                                            <button v-on:click='wb_typeModal_Open'><span
                                                    :title="'Click to open ' + wb_selectedinteraction.type"
                                                    data-toggle="tooltip"
                                                    data-placement="top"
                                                    class="text-slate-600 font-sans text-nowrap font-medium text-base dark:text-slate-200 hover:text-blue-900">{{ wb_selectedinteraction.name }}</span></button>
                                            <div>
                                            </div>
                                        </div>
                                        <span class="text-slate-400 flex items-center justify-start gap-2 font-sans font-medium text-[0.75rem]">+{{
										wb_selectedinteraction.receiver_id }}</span>
                                    </div>
                                </div>
                                <!-- 2nd -->
                                <div class="flex flex-1 justify-start items-start gap-2" v-cloak>
                                    <div class="flex flex-col justify-start items-start gap-2 relative ">
                                        <div class="mainsidebar-badge">
                                            <div class="flex justify-start items-start gap-2 mb-4">
                                                <span
                                                    :class="{
                                                        'bg-violet-100 text-purple-800': wb_selectedinteraction.type === 'leads',
                                                        'bg-red-100 text-red-800': wb_selectedinteraction.type === 'contacts',
                                                    }"
                                                    class="inline-block  text-xs font-semibold px-2 rounded ">
                                                    {{ wb_selectedinteraction.type }}
                                                </span>
                                                <span v-if="wb_selectedinteraction.type === 'leads'"
                                                    class="inline-block  text-xs font-semibold px-2 rounded bg-sky-200 text-sky-800">
                                                    {{ badgeSourceName }}
                                                </span>
                                                <span v-if="wb_selectedinteraction.type === 'leads'"
                                                    :style="{ color: badgeStatusColor }"
                                                    class="inline-block  text-xs font-semibold px-2 rounded bg-slate-200">
                                                    {{ badgeStatusName }}
                                                </span>
                                                <span v-if="wb_selectedinteraction.type === 'contacts'"
                                                    v-for="(groupName, index) in badgeGroupNames" :key="index"
                                                    class="inline-block text-xs font-semibold px-2 rounded bg-blue-100 text-blue-800 ">
                                                    {{ groupName }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="mainsidebar-class">
                                            <div v-if="wb_selectedinteraction.last_msg_time" class="w-[422px] flex justify-end items-center absolute top-[20px] left-[212px]">
                                                <span v-html="wb_alertTime(wb_selectedinteraction.last_msg_time)" class="text-green-500">
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- 3rd -->
                                <?php if (is_admin()) { ?>
                                    <div class="flex gap-3 items-center justify-end ">
                                        <div v-if="wb_selectedinteraction.agent_name.agent_name" class="flex items-center">

                                            <div class="inline-flex items-center space-x-2">
                                                <div class="flex -space-x-1" v-html="wb_selectedinteraction.agent_icon"></div>
                                            </div>
                                        </div>
                                        <button type="button" v-on:click="wb_initAgent" class="dark:text-gray-200">
                                            <i class="fa-solid fa-user-pen fa-lg"
                                                data-toggle="tooltip"
                                                data-placement="left"
                                                title="<?php echo _l('change_support_agent'); ?>"></i>
                                        </button>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="mainsidebar-class" v-cloak>
                                <div class="w-[422px] flex justify-end items-center absolute top-[70px] left-[33%] z-10 ">
                                    <div v-if="overdueAlert" v-html="overdueAlert" v-cloak></div>
                                </div>
                            </div>
                            <?php if (is_admin()) { ?>
                                <div class="modal fade" id="AgentModal" role="dialog">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title"><?php echo _l('modal_title'); ?></h4>
                                            </div>
                                            <div class="modal-body">

                                                <?= render_select(
                                                    'assigned[]',
                                                    $members,
                                                    ['staffid', ['firstname', 'lastname']],
                                                    '',
                                                    '',
                                                    ['data-width' => '100%', 'multiple' => true, 'data-actions-box' => true],
                                                    [],
                                                    '',
                                                    '',
                                                    false
                                                ); ?>

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo _l('close_btn'); ?></button>
                                                <button type="button" class="btn btn-primary" data-dismiss="modal" v-on:click="wb_handleAssignedChange"><?php echo _l('save_btn'); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <div :class="['flex flex-col', is_pemission_replay_input ? 'sm:h-[calc(100vh_-_263px)]' : 'sm:h-[calc(100vh_-_156px)]']"
                                v-cloak>
                                <!-- chat-section Start -->
                                <div class="relative h-full overflow-y-auto"
                                    :class="[
                                                is_pemission_replay_input ? 'sm:h-[calc(100vh_-_263px)]' : 'sm:h-[calc(100vh_-_156px)]',
                                                darkMode ? 'bg-dark-mode' : 'bg-light-mode'
                                            ]"
                                    ref="wb_chatContainer">
                                    <div class="space-y-5 p-4 sm:pb-0 pb-[68px] sm:min-h-[300px] min-h-[400px] mobile-view" v-cloak>
                                        <div v-if="wb_selectedinteraction && wb_selectedinteraction.messages">
                                            <div v-for="(message, index) in wb_selectedinteraction.messages" :key="index" v-cloak>
                                                <!-- Message from the left -->
                                                <div class="flex justify-center" v-if="wb_shouldShowDate(message, wb_selectedinteraction.messages[index - 1])">
                                                    <span class="bg-white py-1 px-2 text-xs rounded-md dark:bg-gray-600 dark:text-gray-200">
                                                        {{getDate(message.time_sent) }}
                                                    </span>
                                                </div>
                                                <div :class="['flex', message.sender_id === wb_selectedinteraction.wa_no ? 'justify-end mb-8 ' : 'justify-start mb-4']">
                                                    <div :class="[
                                                            'border border-gray-300 p-1 break-words rounded-lg max-w-xs',
                                                            message.sender_id === wb_selectedinteraction.wa_no ? 'bg-[#82ee8aa6] dark:bg-[#128C7E]' : 'bg-white dark:bg-[#273443]',
                                                            message.staff_id == 0 && message.sender_id === wb_selectedinteraction.wa_no ? 'bg-[#e7e7e7] dark:bg-[#707070fa]' : '',
                                                            message.type === 'text' && message.message.length > 50 ? 'max-w-xs' : ''
                                                        ]" v-bind="message.sender_id === wb_selectedinteraction.wa_no ? {
                                                            'data-toggle': 'tooltip',
                                                            'data-placement': 'left',
                                                            'title': message.staff_name
                                                        } : {}">
                                                        <template v-if="message.ref_message_id">
                                                            <div class="bg-neutral-100 dark:bg-gray-500 rounded-lg mb-2">
                                                                <div class="flex flex-col gap-2 p-2">
                                                                    <span class="text-gray-400 dark:text-gray-400 font-normal pb-0"><?php echo _l('replying_to'); ?></span>
                                                                    <span class="text-gray-800 dark:text-gray-200 pb-0" v-html="getOriginalMessage(message.ref_message_id).message"></span>
                                                                    <div v-if="getOriginalMessage(message.ref_message_id).assets_url">
                                                                        <template v-if="getOriginalMessage(message.ref_message_id).type === 'image'">
                                                                            <a :href="getOriginalMessage(message.ref_message_id).asset_url" data-lightbox="image-group" target="_blank">
                                                                                <img :src="getOriginalMessage(message.ref_message_id).asset_url" class="rounded-lg max-w-xs max-h-28" alt="Image">
                                                                            </a>
                                                                        </template>
                                                                        <template v-if="getOriginalMessage(message.ref_message_id).type === 'video'">
                                                                            <video :src="getOriginalMessage(message.ref_message_id).asset_url" controls class="rounded-lg max-w-xs max-h-28"></video>
                                                                        </template>
                                                                        <template v-if="getOriginalMessage(message.ref_message_id).type === 'document'">
                                                                            <a :href="getOriginalMessage(message.ref_message_id).asset_url" target="_blank" class="text-blue-500 underline"><?php echo _l('download_document'); ?></a>
                                                                        </template>
                                                                        <template v-if="getOriginalMessage(message.ref_message_id).type === 'audio'">
                                                                            <audio controls class="w-[250px]">
                                                                                <source :src="getOriginalMessage(message.ref_message_id).asset_url" type="audio/mpeg">
                                                                            </audio>
                                                                        </template>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <!-- Conditional rendering for different message types -->
                                                        <template v-if="message.type === 'interactive'">
                                                            <p class="text-gray-800 dark:text-white text-sm">{{ message.message }}</p>
                                                        </template>

                                                        <template v-if="message.type === 'text'">
                                                            <p v-if="message.staff_id != 0"
                                                                v-html="formatMessage(message.message)"
                                                                class="text-gray-800 dark:text-white text-sm"></p>
                                                            <!-- Display message without <br> formatting if the condition is met -->
                                                            <p v-else class="text-gray-800 dark:text-white text-sm" v-html="message.message"></p>
                                                        </template>

                                                        <template v-if="message.type === 'button'">
                                                            <p class="text-gray-800 dark:text-white text-sm" v-html="message.message"></p>
                                                        </template>

                                                        <template v-if="message.type === 'reaction'">
                                                            <p class="text-gray-800 dark:text-white text-sm" v-html="message.message"></p>
                                                        </template>

                                                        <template v-else-if="message.type === 'image'">
                                                            <a :href="message.asset_url" data-lightbox="image-group" target="_blank">
                                                                <img :src="message.asset_url" alt="Image" class="rounded-lg max-w-xs max-h-28">
                                                            </a>
                                                            <p class="text-gray-600 text-xs mt-2 dark:text-gray-200" v-if="message.caption">{{ message.caption }}</p>
                                                        </template>

                                                        <template v-else-if="message.type === 'video'">
                                                            <video :src="message.asset_url" controls class="rounded-lg max-w-xs max-h-28"></video>
                                                            <p class="text-gray-600 text-xs mt-2 dark:text-gray-200" v-if="message.message">{{ message.message }}</p>
                                                        </template>

                                                        <template v-else-if="message.type === 'document'">
                                                            <a :href="message.asset_url" target="_blank" class="text-blue-500 underline "><?php echo _l('download_document'); ?></a>
                                                        </template>

                                                        <template v-else-if="message.type === 'audio'">
                                                            <audio controls class="w-[300px]">
                                                                <source :src="message.asset_url" type="audio/mpeg">
                                                            </audio>
                                                            <p class="text-gray-600 text-xs mt-2 dark:text-gray-200" v-if="message.message">{{ message.message }}</p>
                                                        </template>

                                                        <!-- Message Timestamp and Status -->
                                                        <div class="flex justify-between items-center gap-4 mt-2 text-xs text-gray-600 dark:text-gray-200">
                                                            <span>{{ wb_getTime(message.time_sent) }}</span>
                                                            <div>
                                                                <span v-on:click="replyToMessage(message)" class="cursor-pointer">
                                                                    <i class="fa-solid fa-reply"></i>
                                                                </span>
                                                                <span v-if="message.sender_id === wb_selectedinteraction.wa_no" class="ml-2">
                                                                    <i v-if="message.status === 'sent'" class="fa fa-check text-gray-500 dark:text-white" title="Sent"></i>
                                                                    <i v-else-if="message.status === 'delivered'" class="fa fa-check-double text-gray-500 dark:text-white" title="Delivered"></i>
                                                                    <i v-else-if="message.status === 'read'" class="fa fa-check-double text-cyan-500" title="Read"></i>
                                                                    <i v-else-if="message.status === 'failed'" class="fa fa-exclamation-circle text-red-500" title="Failed"></i>
                                                                    <i v-else-if="message.status === 'deleted'" class="fa fa-trash text-red-500" title="Deleted"></i>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="replyingToMessage" class="flex justify-center w-full" v-cloak>
                                    <div class=" w-11/12 bg-[#F0F2F5] dark:bg-gray-500 rounded-lg ">
                                        <div class="bg-white dark:bg-gray-500 w-full max-w-full p-3 rounded-lg shadow-lg flex justify-between items-center mb-1">
                                            <div class="flex flex-col gap-2">
                                                <span class="text-gray-400 font-normal dark:text-gray-300"><?php echo _l('replying_to'); ?></span>
                                                <span class="text-gray-800 font-semibold dark:text-gray-200" v-html="replyingToMessage.message"></span>
                                                <div v-if="replyingToMessage.asset_url">
                                                    <template v-if="replyingToMessage.type === 'image'">
                                                        <img :src="replyingToMessage.asset_url" class="rounded-lg max-w-xs max-h-28" alt="Image">
                                                    </template>
                                                    <template v-if="replyingToMessage.type === 'video'">
                                                        <video :src="replyingToMessage.asset_url" controls class="rounded-lg max-w-xs max-h-28"></video>
                                                    </template>
                                                    <template v-if="replyingToMessage.type === 'document'">
                                                        <a :href="replyingToMessage.asset_url" target="_blank" class="text-blue-500 underline"><?php echo _l('download_document'); ?></a>
                                                    </template>
                                                    <template v-if="replyingToMessage.type === 'audio'">
                                                        <audio controls class="w-[250px]">
                                                            <source :src="replyingToMessage.asset_url" type="audio/mpeg">
                                                        </audio>
                                                    </template>
                                                </div>
                                            </div>
                                            <button v-on:click="clearReply">
                                                <i class="fa-regular fa-2xl fa-circle-xmark"></i>
                                            </button>
                                        </div>
                                        <ul v-if="showQuickReplies" class="flex-grow bg-white shadow-md rounded-lg mt-2 p-2">
                                            <li v-for="(reply, index) in filteredQuickReplies"
                                                :key="index"
                                                v-on:click="selectQuickReply(index)"
                                                :class="{
													'bg-blue-100 text-blue-900': index === quickReplyIndex,
													'cursor-pointer rounded-md p-2 transition-all duration-200 ease-in-out': true
													}">
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Preview section Start -->
                            <div class="relative" v-cloak>
                                <div class="absolute bottom-[-15px] " v-cloak>
                                    <div v-if="wb_imageAttachment || wb_videoAttachment || wb_documentAttachment" class="flex flex-wrap gap-4 p-4">
                                        <!-- Image Attachment -->
                                        <div v-if="wb_imageAttachment" class="relative flex flex-col items-center py-6 px-4 bg-[#F0F2F5] dark:bg-gray-600 border border-gray-300 rounded-lg shadow-lg max-w-[250px]">
                                            <!-- Preview Text -->
                                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-200 mb-2"><?php echo _l('preview'); ?></span>
                                            <button v-on:click="wb_removeImageAttachment" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 mt-1 focus:outline-none">
                                                <i class="fa fa-times fa-lg"></i>
                                            </button>
                                            <img :src="wb_imagePreview" alt="Selected Image" class="w-full h-28 object-cover rounded-md mb-3 shadow-sm" />
                                            <span class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-200 truncate w-full text-center">{{ wb_imageAttachment.name }}</span>
                                        </div>
                                        <!-- Video Attachment -->
                                        <div v-if="wb_videoAttachment" class="relative flex flex-col items-center py-6 px-4 bg-[#F0F2F5] dark:bg-gray-600 border border-gray-300 rounded-lg shadow-md max-w-[280px]">
                                            <!-- Preview Text -->
                                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-200 mb-2"><?php echo _l('preview'); ?></span>
                                            <button v-on:click="wb_removeVideoAttachment" class="absolute top-2 right-2 text-red-500 hover:text-red-700 focus:outline-none">
                                                <i class="fa fa-times "></i>
                                            </button>
                                            <video :src="wb_videoPreview" controls class="w-full object-cover rounded-md"></video>
                                            <span class="mt-2 text-sm dark:text-gray-200 text-gray-700 truncate w-full text-center">{{ wb_videoAttachment.name }}</span>
                                        </div>
                                        <!-- Document Attachment -->
                                        <div v-if="wb_documentAttachment" class="relative flex flex-col items-center p-2 bg-[#F0F2F5] dark:bg-gray-600 border border-gray-300 rounded-lg shadow-md max-w-[250px] min-w-[200px]">
                                            <!-- Preview Text -->
                                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-200 mb-2"><?php echo _l('preview'); ?></span>
                                            <button v-on:click="wb_removeDocumentAttachment" class="absolute top-2 right-2 text-red-500 hover:text-red-700 focus:outline-none">
                                                <i class="fa fa-times"></i>
                                            </button>
                                            <i class="fa fa-file text-gray-600  text-4xl"></i>
                                            <span class="mt-2 text-sm text-gray-700 truncate w-full text-center dark:text-gray-200">{{ wb_documentAttachment.name }}</span>
                                        </div>
                                    </div>
                                </div>
                                <!-- Preview section End -->
                            </div>

                            <!-- reply section start-->
                            <div v-if="wb_selectedinteraction && wb_selectedinteraction.messages && is_pemission_replay_input" class="right-bottom w-full top-full sticky flex justify-between items-center px-2 py-2 rounded dark:bg-[#1E293B] bg-white z-10" v-cloak>
                                <form v-on:submit.prevent="wb_sendMessage" class="flex flex-col w-full" v-cloak>
                                    <!-- Input Field at the Top -->
                                    <div class="w-full dark:bg-gray-600  bg-gray-100 rounded-lg px-4 py-1 text-sm">
                                        <textarea
                                            v-model="wb_newMessage"
                                            ref="inputField"
                                            placeholder="<?= _l('type_your_message'); ?>"
                                            class="mentionable w-full bg-transparent focus:outline-none px-2 py-2 h-[40px] dark:text-white resize-none text-sm placeholder-center"
                                            id="wb_newMessage"
                                            @keydown="handleKeyPress"></textarea>
                                    </div>
                                    <div class="flex justify-between items-center space-x-4" v-cloak>
                                        <!-- Left Side Icon Column -->
                                        <div class="flex space-x-2 items-center">
                                            <!-- OpenAI Button (if enabled) -->
                                            <?php if (get_option('enable_wb_openai')) { ?>
                                                <div class="dropup" tabindex="0" data-toggle="tooltip" data-title='<?php echo _l('ai_prompt_note'); ?>'>
                                                    <button class="btn dropdown-toggle p-2" :class="{ 'disabled': !isButtonEnabled }" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa-solid fa-robot text-violet-500 dark:text-cyan-400 text-xl"></i>
                                                    </button>

                                                    <ul class="dropdown-menu w-[300px] dark:bg-gray-600" aria-labelledby="dropdownMenu2">
                                                        <!-- Menu Items -->
                                                        <li class="dropdown-header">
                                                            <span class="tw-mr-1"><i class="fa-solid fa-robot text-sky-500 dark:text-cyan-400"></i></span><span class="dark:text-gray-200"><?= _l('ai_prompt'); ?></span>
                                                        </li>
                                                        <li role="separator" class="divider dark:bg-gray-400"></li>
                                                        <li class="dropdown dropdown-submenu">
                                                            <a href="javascript:;" class="dark:text-gray-200"><i class="fa-solid fa-headset text-sky-500 dark:text-cyan-400 mr-2"></i>Change Tone</span></a>
                                                            <ul class="dropdown-menu dark:bg-gray-600" style="top: 35px;margin-top: -140px;">
                                                                <li v-on:click="wb_handleItemClick('Change Tone', '<?= _l('professional'); ?>')"><a href="javascript:;" class="dark:text-gray-200"><?= _l('professional'); ?></a></li>
                                                                <li v-on:click="wb_handleItemClick('Change Tone', '<?= _l('friendly'); ?>')"><a href="javascript:;" class="dark:text-gray-200"><?= _l('friendly'); ?></a></li>
                                                                <li v-on:click="wb_handleItemClick('Change Tone', '<?= _l('empathetic'); ?>')"><a href="javascript:;" class="dark:text-gray-200"><?= _l('empathetic'); ?></a></li>
                                                                <li v-on:click="wb_handleItemClick('Change Tone', '<?= _l('straightforward'); ?>')"><a href="javascript:;" class="dark:text-gray-200"><?= _l('straightforward'); ?></a></li>
                                                            </ul>
                                                        </li>
                                                        <li class="dropdown-submenu">
                                                            <a href="javascript:;" class="dark:text-gray-200">
                                                                <i class="fa-solid fa-language text-sky-500 dark:text-cyan-400 tw-mr-2"></i>
                                                                Translate
                                                            </a>
                                                            <ul class="dropdown-menu dropdown-menu dark:bg-gray-600" style="top: 35px; margin-top: -350px;">
                                                                <li>
                                                                    <input type="text" class="form-control dark:bg-gray-700" style="border-radius: 25px;" v-model="searchQuery" placeholder="<?= _l('search_language'); ?>" />
                                                                </li>
                                                                <li v-for="lang in filteredLanguages" :key="lang">
                                                                    <a href="javascript:;" class="dark:text-gray-200" v-on:click="wb_handleItemClick('Translate', lang)">
                                                                        {{ ucfirst(lang) }}
                                                                    </a>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                        <li v-on:click="wb_handleItemClick('Fix Spelling & Grammar')"><a href="javascript:;" class="dark:text-gray-200"><i class="fa-solid fa-check text-sky-500 dark:text-cyan-400 tw-mr-2"></i>Fix Spelling & Grammar</a></li>
                                                        <li v-on:click="wb_handleItemClick('Simplify Language')"><a href="javascript:;" class="dark:text-gray-200"><i class="fa-solid fa-virus text-sky-500 dark:text-cyan-400 tw-mr-2"></i>Simplify Language</a></li>
                                                        <li class="dropdown dropdown-submenu" v-if="customPrompts.length > 0">
                                                            <a href="javascript:;" class="dark:text-gray-200"><i class="fa-solid fa-reply text-sky-500 dark:text-cyan-400 mr-2"></i>Custom Prompt</a>
                                                            <ul class="dropdown-menu dark:bg-gray-600" style="top: 35px;margin-top: -80px;">
                                                                <li v-for="(prompt, index) in customPrompts" :key="index" v-if="shouldDisplayPrompt(prompt)" v-on:click="wb_handleItemClick('Custom Prompt', prompt.action)">
                                                                    <a href="javascript:;" class="dark:text-gray-200">{{ prompt.label }}</a>
                                                                </li>
                                                            </ul>
                                                        </li>
                                                    </ul>
                                                </div>
                                            <?php } ?>
                                            <!-- Emoji Button -->
                                            <button v-on:click="toggleEmojiPicker" id="emoji_btn" type="button" class="flex justify-center items-center cursor-pointer dark:text-gray-200 hover:text-blue-900">
                                                <i class="fa-regular fa-face-grin fa-xl" data-toggle="tooltip" data-title="<?= _l('emojis'); ?>" data-placement="top"></i>
                                            </button>

                                            <!-- Attachment Button -->
                                            <button v-on:click="toggleAttachmentOptions" type="button" class="flex items-center justify-center p-2 dark:text-gray-200 text-gray-700 hover:text-blue-900 focus:outline-none" title="<?= _l('attach_image_video_docs'); ?>" data-toggle="tooltip" data-title="<?= _l('attach_image_video_docs'); ?>" data-placement="top">
                                                <i class="fa-solid fa-paperclip fa-xl"></i>
                                            </button>
                                            <!-- Display the canned replies list -->
                                            <ul v-if="cannedRepliesVisible && cannedReplies.length" class="absolute bottom-[96px] overflow-y-auto left-[120px] bg-white dark:bg-gray-600 rounded-lg shadow-lg flex flex-col space-y-2 p-4 w-[500px] max-h-[400px] mobile-canned" id="canned_replies" v-cloak>
                                                <div class="bg-blue-500 dark:bg-cyan-500 text-white text-lg font-bold px-4 py-2 rounded">
                                                    <?= _l('canned_replies'); ?>
                                                </div>
                                                <li v-for="reply in cannedReplies"
                                                    v-if="shoud_wb_cannedReplyData(reply)"
                                                    :key="reply.title"
                                                    class="relative flex flex-col px-3 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer rounded-md mb-2 shadow-sm" :class="{'custom-border': darkMode}"
                                                    v-on:click="addToMessage(reply)">
                                                    <div class="font-semibold text-gray-800 dark:text-white truncate w-[385px]">{{ reply.title }}</div>
                                                    <div class="text-gray-600 dark:text-white text-sm truncate">{{ reply.description }}</div>
                                                    <span
                                                        v-if="reply.is_public === '1'"
                                                        class="absolute top-[1px] right-2 bg-green-200 text-green-800 text-xs font-semibold px-2 py-1 rounded">
                                                        <?= _l('public'); ?>
                                                    </span>
                                                </li>
                                            </ul>
                                            <!-- Canned Button -->
                                            <button v-if="cannedReplies.length > 0" v-on:click="toggleCannedReplies" ref="cannedRepliesDropdown" type="button" class="flex items-center justify-center p-2 text-gray-700 dark:text-gray-200 hover:text-blue-900 focus:outline-none" title="<?= _l('canned_reply'); ?>" data-toggle="tooltip" data-title="<?= _l('attach_image_video_docs'); ?>" data-placement="top">
                                                <i class="fa-regular fa-message fa-lg"></i>
                                            </button>
                                            <!-- Recording Button -->
                                            <button v-on:click="wb_toggleRecording" type="button" class="flex items-center justify-center p-2 text-gray-700 dark:text-gray-200 hover:text-gray-900 focus:outline-none" title="<?= _l('record_audio'); ?>">
                                                <span v-if="!wb_recording" class="fa fa-microphone text-xl" aria-hidden="true" data-toggle="tooltip" data-title="<?= _l('record_audio'); ?>" data-placement="top"></span>
                                                <span v-else class="fa fa-stop text-xl" aria-hidden="true"></span>
                                            </button>
                                        </div>
                                        <div class="flex justify-end items-center gap-4">
                                            <div class="text-sm text-gray-500 dark:text-gray-200 font-semibold mainsidebar-class"><?php echo _l('use_@_to_add_merge_fields'); ?></div>
                                            <!-- Send Button (conditionally visible) -->
                                            <button v-if="wb_showSendButton || wb_audioBlob" type="submit" class="flex items-center justify-center p-2 bg-green-500 rounded-full focus:outline-none">
                                                <i class="fa fa-paper-plane text-white dark:text-gray-900" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                        <div class="absolute bottom-[100px]" id="all_atech" v-cloak>
                                            <!-- Attachment Options Dropdown (conditionally visible) -->
                                            <div v-if="showAttachmentOptions" class="flex flex-col gap-2 text-nowrap bg-[#F0F2F5] dark:bg-gray-600 shadow-lg rounded-lg p-2">
                                                <input type="file" id="imageAttachmentInput" ref="imageAttachmentInput" v-on:change="wb_handleImageAttachmentChange"
                                                    accept="<?= wb_get_allowed_extension()['image']['extension']; ?>" class="hidden">
                                                <label for="imageAttachmentInput" class="cursor-pointer flex items-center p-2 text-gray-700 dark:text-gray-200 hover:text-gray-900">
                                                    <i class="fa-regular text-blue-500 fa fa-image mr-2 fa-lg" aria-hidden="true"></i><span><?= _l('send_image'); ?></span>
                                                </label>

                                                <input type="file" id="videoAttachmentInput" ref="videoAttachmentInput" v-on:change="wb_handleVideoAttachmentChange"
                                                    accept="<?= wb_get_allowed_extension()['video']['extension']; ?>" class="hidden">
                                                <label for="videoAttachmentInput" class="cursor-pointer flex items-center p-2 text-gray-700 dark:text-gray-200 hover:text-gray-900">
                                                    <i class="fa fa-video text-green-500 mr-2 fa-lg" aria-hidden="true"></i><span><?= _l('send_video'); ?></span>
                                                </label>

                                                <input type="file" id="documentAttachmentInput" ref="documentAttachmentInput" v-on:change="wb_handleDocumentAttachmentChange"
                                                    accept="<?= wb_get_allowed_extension()['document']['extension']; ?>" class="hidden">
                                                <label for="documentAttachmentInput" class="cursor-pointer flex items-center p-2 text-gray-700 dark:text-gray-200 hover:text-gray-900">
                                                    <i class="fa-regular text-yellow-500 fa fa-file mr-2 fa-lg" aria-hidden="true"></i><span><?= _l('send_document'); ?></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="emoji-picker-container" ref="emojiPickerContainer" v-cloak></div>
                                    <input type="hidden" name="rel_type" id="rel_type" value="">
                                </form>
                            </div>
                            <!-- reply section end-->
                            <!-- Chat-section End -->
                        </div>
                    </template>
                </div>
                <!-- Main content end-->
            </div>
        </div>
    </div>
</div>
<div id="contact_data"></div>
<?php init_tail(); ?>
<?php $this->load->view('admin/clients/client_js'); ?>
<script src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/vue.min.js') . '?v=' . $module_version; ?>"></script>
<script src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/axios.min.js') . '?v=' . $module_version; ?>"></script>
<script src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/recorder-core.js') . '?v=' . $module_version; ?>"></script>
<script src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/purify.min.js') . '?v=' . $module_version; ?>"></script>
<script src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/mp3-engine.js') . '?v=' . $module_version; ?>"></script>
<script src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/mp3.js') . '?v=' . $module_version; ?>"></script>
<script src="<?php echo module_dir_url(WHATSBOT_MODULE, 'assets/js/emoji-mart.min.js') . '?v=' . $module_version; ?>"></script>

<script>
    "use strict";
    $(document).on('click', '.hideMessage', function() {
        $(this).parent().addClass('hide');
    });

    new Vue({
        el: '#app',
        data() {
            return {
                interactions: [],
                previousCounts: {},
                wb_selectedinteractionIndex: null,
                wb_selectedinteraction: null,
                wb_selectedinteractionMobNo: null,
                wb_selectedinteractionSenderNo: null,
                wb_newMessage: '',
                wb_agentId: '',
                wb_selectedStaffId: '',
                wb_selectedinteractionId: null,
                wb_imageAttachment: null,
                wb_videoAttachment: null,
                wb_documentAttachment: null,
                wb_imagePreview: '',
                wb_videoPreview: '',
                wb_csrfToken: '<?php echo $csrfToken; ?>',
                wb_recording: false,
                wb_audioBlob: null,
                wb_recordedAudio: null,
                errorMessage: '',
                sameNumErrorMessage: '',
                wb_searchText: '',
                wb_login_staff_id: '<?= get_staff_user_id(); ?>',
                wb_selectedWaNo: '<?= get_option("wac_default_phone_number"); ?>', // Define wb_selectedWaNo variable
                wb_default_number: '<?= get_option("wac_default_phone_number"); ?>',
                wb_filteredInteractions: [], // Define wb_filteredInteractions to store filtered interactions
                wb_displayedInteractions: [],
                wb_showEmojiPicker: false,
                isLoading: false,
                showQuickReplies: false,
                filteredQuickReplies: [],
                languages: <?= json_encode(config_item('languages')); ?>,
                searchQuery: '',
                showAttachmentOptions: false,
                replyingToMessage: null,
                cannedReplies: [],
                customPrompts: [],
                cannedRepliesVisible: true,
                has_pemission_view_canned_reply: '<?= staff_can('view', 'wtc_canned_reply'); ?>',
                has_pemission_view_ai_prompts: '<?= staff_can('view', 'wtc_ai_prompts'); ?>',
                //new_chat
                isShowChatMenu: false,
                isShowUserChat: false,
                darkMode: false,
                selectedTab: 'searching',
                wb_reltype_filter: '',
                wb_contact_groups: '',
                wb_lead_sources: '',
                wb_lead_status: '',
                wb_agents_filter: '',
                noResultsMessage: '',
                badgeSourceName: '',
                badgeStatusName: '',
                badgeStatusColor: '',
                badgeGroupNames: [],
                wb_requireData: [],
                wb_pusher_api_key: '<?= get_option('pusher_app_key'); ?>',
                wb_pusher_cluster: '<?= get_option('pusher_cluster'); ?>',
                is_pemission_replay_input: !('<?= !is_admin() && staff_can('view_own', 'wtc_chat'); ?>'),
            };
        },

        methods: {
            handlerequiredata() {
                $.ajax({
                    url: '<?= admin_url('whatsbot/get_chat_required_data'); ?>',
                    type: 'POST',
                    dataType: 'json',
                    success: (response) => {
                        this.wb_requireData = response;
                    },
                })
            },
            handleBadgeDisplay() {
                let filteredInteractions = [this.wb_selectedinteraction];
                this.badgeGroupNames = [];
                filteredInteractions.forEach(interaction => {
                    // Check if the interaction type is 'contacts'
                    if (interaction.type === "contacts") {
                        if (interaction.group) {
                            // Iterate through each group in interaction.groups
                            const groupIds = interaction.group.map(item => item.groupid);
                            groupIds.forEach(groupId => {
                                const matchingContactGroup = this.wb_requireData && this.wb_requireData.contact_groups ? this.wb_requireData.contact_groups.find(contactGroup => contactGroup.id === groupId) : undefined;
                                if (matchingContactGroup) {
                                    this.badgeGroupNames.push(matchingContactGroup.name);
                                }
                            });

                        }
                    } else if (interaction.type === "leads") {
                        // Your existing logic for leads

                        let matchingSource = this.wb_requireData && this.wb_requireData.lead_sources ?
                            this.wb_requireData.lead_sources.find(source => source.id === interaction.source) :
                            undefined;
                        if (matchingSource) {
                            this.badgeSourceName = matchingSource.name;
                        }

                        let matchingStatus = this.wb_requireData && this.wb_requireData.lead_status ?
                            this.wb_requireData.lead_status.find(status => status.id === interaction.status) :
                            undefined;
                        if (matchingStatus) {
                            this.badgeStatusName = matchingStatus.name;
                            this.badgeStatusColor = matchingStatus.color;
                        }
                    }
                });
            },

            toggleDarkMode() {
                this.darkMode = !this.darkMode;
                document.documentElement.classList.toggle('dark', this.darkMode);
                localStorage.setItem('darkMode', this.darkMode.toString());
            },
            formatMessage(text) {
                // Replace newline characters with <br> for display
                return text.replace(/\n/g, '<br>');
            },

            handleKeyPress(event) {
                if (event.keyCode === 13) { // Enter key
                    if (event.shiftKey) {
                        // Shift + Enter adds a newline
                        event.preventDefault();
                        this.wb_newMessage += '\n';
                    } else {
                        // Enter alone sends the message
                        event.preventDefault();
                        if (this.wb_newMessage.trim() !== '') {
                            this.wb_sendMessage(); // Call your message sending function
                            this.wb_newMessage = ''; // Clear the message after sending
                        }
                    }
                }
            },

            wb_typeModal_Open() {
                if (this.wb_selectedinteraction.type === 'contacts') {
                    contact(this.wb_selectedinteraction.client_id, this.wb_selectedinteraction.type_id);
                } else if (this.wb_selectedinteraction.type === 'leads') {
                    init_lead(this.wb_selectedinteraction.type_id);
                }
            },
            wb_fetchCustomPrompts() {
                $.ajax({
                    url: `${admin_url}whatsbot/ai_prompts/get`,
                    type: 'POST',
                    dataType: 'json',
                    success: (response) => {
                        if (Array.isArray(response.custom_prompts)) {
                            this.customPrompts = response.custom_prompts.map(prompt => ({
                                label: prompt.name,
                                action: prompt.action,
                                is_public: prompt.is_public,
                                added_from: prompt.added_from

                            }));
                        } else {
                            console.error('Invalid response structure', response);
                        }
                    },
                    error: (error) => {
                        console.error('Error fetching canned replies:', error);
                    }
                });
            },
            shouldDisplayPrompt(prompt) {
                return this.wb_login_staff_id === prompt.added_from || this.has_pemission_view_ai_prompts;
            },

            toggleCannedReplies() {
                this.cannedRepliesVisible = !this.cannedRepliesVisible;
            },
            wb_cannedReplyData() {
                $.ajax({
                    url: `${admin_url}whatsbot/canned_reply/get`,
                    type: 'POST',
                    dataType: 'html',
                    success: (response) => {
                        const parsedResponse = JSON.parse(response);
                        if (parsedResponse.reply_data && Array.isArray(parsedResponse.reply_data)) {
                            this.cannedReplies = parsedResponse.reply_data.map(reply => ({
                                title: reply.title,
                                description: reply.description,
                                is_public: reply.is_public,
                                added_from: reply.added_from
                            }));
                            this.cannedRepliesVisible = false;
                        } else {
                            console.error('Invalid response structure');
                        }
                    },
                    error: (error) => {
                        console.error('Error fetching canned replies:', error);
                    }
                });
            },
            shoud_wb_cannedReplyData(reply) {
                return reply.is_public === '1' || this.wb_login_staff_id === reply.added_from || this.has_pemission_view_canned_reply;
            },
            addToMessage(reply) {
                this.wb_newMessage = `${reply.description}`;
                this.cannedRepliesVisible = false;
                this.$refs.inputField.focus();
            },

            wb_selectinteraction(id) {
                $.ajax({
                    url: `${admin_url}whatsbot/chat_mark_as_read`,
                    type: 'POST',
                    dataType: 'html',
                    data: {
                        'interaction_id': id
                    },
                })
                const index = this.interactions.findIndex(interaction => interaction.id === id);
                if (index !== -1) {
                    this.wb_selectedinteractionIndex = index;
                    this.wb_selectedinteraction = this.interactions[index];
                    this.wb_selectedinteraction.messages.forEach(message => {
                        if (message.is_read == 0) {
                            message.is_read = 1;
                        }
                    });
                    this.wb_selectedinteractionId = this.wb_selectedinteraction['id'];
                    this.wb_selectedinteractionMobNo = this.wb_selectedinteraction['receiver_id'];
                    this.wb_selectedinteractionSenderNo = this.wb_selectedinteraction['wa_no'];
                    this.wb_scrollToBottom();
                    this.handleBadgeDisplay();
                    this.wb_fetchCustomPrompts();
                    this.wb_cannedReplyData();
                    this.isShowChatMenu = false;
                    this.isShowUserChat = true;
                    this.$nextTick(() => {
                        $('#rel_type').val(this.wb_selectedInteraction['type']);
                        $('#rel_type').trigger('change');
                    });
                }
            },

            sanitizeMessage(message) {
                return DOMPurify.sanitize(message, {
                    USE_PROFILES: {
                        html: true
                    }
                });
            },

            trimMessage(message, maxLength = 100) {
                const sanitizedMessage = this.sanitizeMessage(message);
                if (sanitizedMessage.length > maxLength) {
                    return sanitizedMessage.substring(0, maxLength) + '...';
                }
                return sanitizedMessage;
            },

            getOriginalMessage(refMessageId) {
                const message = this.wb_selectedinteraction.messages.find(msg => msg.message_id === refMessageId) || {};
                return {
                    ...message,
                    message: this.trimMessage(message.message),
                    assets_url: message.asset_url || ''
                };
            },
            replyToMessage(message) {
                this.replyingToMessage = message || message.asset_url;
                this.wb_scrollToBottom();
            },
            clearReply() {
                this.replyingToMessage = null;
            },
            wb_initAgent() {
                const agentId = this.wb_selectedinteraction.agent.agent_id;
                this.selectedStaffId = agentId;
                $('#AgentModal').modal('show');
                setTimeout(function() {
                    $('#AgentModal').find('select[name="assigned[]"]').val(agentId);
                    $('#AgentModal').find('select[name="assigned[]"]').trigger('change');
                }, 100);
            },
            wb_handleAssignedChange(event) {
                const id = this.wb_selectedinteraction ? this.wb_selectedinteraction.id : null; // Get the current interaction ID
                const staffId = $('select[name="assigned[]"]').val(); // Get the selected staff ID from the dropdown
                // Send the selected staff ID to the server via AJAX
                $.ajax({
                        url: `${admin_url}whatsbot/assign_staff`,
                        type: 'POST',
                        dataType: 'html',
                        data: {
                            'staff_id': staffId, // Send selected staff ID
                            'interaction_id': id // Send interaction ID
                        },
                    })
                    .done((res) => {
                        // After the request is successful, update the agent_id in wb_selectedinteraction
                        if (this.wb_selectedinteraction) {
                            res = JSON.parse(res)

                            // Replace the agent_id in the current interaction's agent object
                            this.wb_selectedinteraction.agent_icon = res.agent_icon;
                            this.wb_selectedinteraction.agent_name = res.agent_name;

                        }
                        // Re-select the interaction to refresh the view
                        this.wb_selectinteraction(id); // Call the method to refresh the selected interaction
                    });
            },
            wb_deleteInteraction(id) {
                if (confirm_delete()) {
                    $.ajax({
                        url: `${admin_url}whatsbot/delete_chat`,
                        type: 'POST',
                        dataType: 'html',
                        data: {
                            'interaction_id': id
                        },
                    }).done((res) => { // Use an arrow function here
                        if (res) {
                            alert_float('danger', "<?= _l('deleted', _l('chat')); ?>");
                            // Remove the interaction from both arrays
                            this.interactions = this.interactions.filter(interaction => interaction.id !== id);
                            this.wb_displayedInteractions = this.interactions;
                            if (this.wb_selectedinteractionId === id) {
                                this.wb_selectedinteraction = null;
                                this.wb_selectedinteractionId = null;
                                this.wb_selectedinteractionIndex = -1;
                                this.wb_selectedinteractionMobNo = null;
                                this.wb_selectedinteractionSenderNo = null;
                                this.isShowUserChat = false; // Hide the chat UI when interaction is deleted
                            }
                        }
                    });
                }
            },
            async wb_sendMessage() {
                if (this.wb_default_number != this.wb_selectedinteraction.wa_no) {
                    this.sameNumErrorMessage = "<?= _l('you_cannot_send_a_message_using_this_number'); ?>";
                    return;
                }
                if (!this.wb_selectedinteraction) return;
                const wb_formData = new FormData();
                wb_formData.append('id', this.wb_selectedinteraction.id);
                wb_formData.append('to', this.wb_selectedinteraction.receiver_id);
                wb_formData.append('csrf_token_name', this.wb_csrfToken);
                wb_formData.append('type', this.wb_selectedinteraction.type);
                wb_formData.append('type_id', this.wb_selectedinteraction.type_id);
                const MAX_MESSAGE_LENGTH = 2000;
                if (this.wb_newMessage.length > MAX_MESSAGE_LENGTH) {
                    this.wb_newMessage = this.wb_newMessage.substring(0, MAX_MESSAGE_LENGTH);
                }
                // Add message if it exists
                if (this.wb_newMessage.trim()) {
                    wb_formData.append('message', DOMPurify.sanitize(this.wb_newMessage));
                }
                // Handle image attachment
                if (this.wb_imageAttachment) {
                    wb_formData.append('image', this.wb_imageAttachment);
                }
                // Handle video attachment
                if (this.wb_videoAttachment) {
                    wb_formData.append('video', this.wb_videoAttachment);
                }
                // Handle document attachment
                if (this.wb_documentAttachment) {
                    wb_formData.append('document', this.wb_documentAttachment);
                }
                // Handle audio attachment
                if (this.wb_audioBlob) {
                    wb_formData.append('audio', this.wb_audioBlob, 'audio.mp3');
                }
                if (this.replyingToMessage) {
                    wb_formData.append('ref_message_id', this.replyingToMessage.message_id);
                }

                try {
                    const wb_response = await axios.post('<?php echo admin_url('whatsbot/whatsapp_webhook/send_message'); ?>', wb_formData, {
                        headers: {
                            'Content-Type': 'multipart/form-data'
                        }
                    });
                    // Clear attachments
                    this.wb_newMessage = '';
                    this.wb_imageAttachment = null;
                    this.wb_videoAttachment = null;
                    this.wb_documentAttachment = null;
                    this.wb_audioBlob = null;
                    this.wb_imagePreview = '';
                    this.wb_videoPreview = '';
                    this.wb_filterInteractions();
                    this.wb_selectinteraction(this.wb_selectedinteraction.id);
                    this.errorMessage = '';
                    this.clearReply();
                    this.wb_scrollToBottom();
                    this.wb_selectedinteractionIndex = 0;
                } catch (error) {
                    const wb_rawErrorMessage = error.response && error.response.data ? error.response.data : 'An error occurred. Please try again.';
                    // Define regular expressions to match the desired parts of the HTML error message
                    const wb_typeRegex = /<p>Type: (.+)<\/p>/;
                    const wb_messageRegex = /<p>Message: (.+)<\/p>/;
                    // Extract the type and message from the HTML error message
                    const wb_typeMatch = wb_rawErrorMessage.match(wb_typeRegex);
                    var wb_messageMatch = wb_rawErrorMessage.match(wb_messageRegex);
                    if (wb_messageMatch != null) {
                        if (typeof(wb_messageMatch[1] == 'object')) {
                            wb_messageMatch[1] = JSON.parse(wb_messageMatch[1]);
                            wb_messageMatch[1] = wb_messageMatch[1].error.message;
                        }
                    }
                    const wb_getTypeText = wb_typeMatch ? wb_typeMatch[1] : '';
                    const wb_getMessageText = wb_messageMatch ? wb_messageMatch[1] : '';
                    // Construct the error message by concatenating the extracted text content
                    const errorMessage = wb_getTypeText.trim() + '\n' + wb_getMessageText.trim();
                    this.errorMessage = errorMessage;
                }
            },

            async wb_fetchinteractions() {
                try {
                    const staff_id = this.wb_login_staff_id;
                    const wb_response = await fetch('<?php echo admin_url('whatsbot/interactions'); ?>');
                    const data = await wb_response.json();
                    const enable_supportagent = "<?= get_option('enable_supportagent'); ?>";
                    if (data && data.interactions) {
                        const isAdmin = <?php echo is_admin() ? 'true' : 'false'; ?>;
                        if (!isAdmin && enable_supportagent == 1) {
                            this.interactions = data.interactions.filter(interaction => {
                                const chatagent = interaction.agent;
                                if (chatagent) {
                                    const agentIds = Array.isArray(chatagent.agent_id) ? chatagent.agent_id : [chatagent.agent_id];
                                    const assignIds = Array.isArray(chatagent.assign_id) ? chatagent.assign_id : [chatagent.assign_id];
                                    // Check if `staff_id` is included in either `agentIds` or `assignIds`
                                    return agentIds.includes(staff_id) || assignIds.includes(staff_id);
                                }
                                return false;
                            });
                        } else {
                            this.interactions = data.interactions;
                        }
                    } else {
                        this.interactions = [];
                    }
                    this.wb_filterInteractions();
                    this.wb_updateSelectedInteraction();
                } catch (error) {
                    console.error('Error fetching interactions:', error);
                }
            },
            initializePusher() {
                if (!this.wb_pusher_api_key || !this.wb_pusher_cluster) {
                    return; // Exit the method if either is null
                }
                // Initialize Pusher with your app key and cluster
                const pusher = new Pusher(this.wb_pusher_api_key, {
                    cluster: this.wb_pusher_cluster,
                    encrypted: true,
                });
                // Subscribe to the 'interactions-channel'
                const channel = pusher.subscribe('interactions-channel');
                // Listen for the 'interaction-update' event
                channel.bind('new-message-event', (data) => {
                    // Update interactions based on real-time data from Pusher
                    this.appendNewInteractions(data.interaction);
                });
            },
            appendNewInteractions(newInteractions) {
                const staff_id = this.wb_login_staff_id;
                const enable_supportagent = "<?= get_option('enable_supportagent'); ?>";
                const isAdmin = <?php echo is_admin() ? 'true' : 'false'; ?>;
                const existingInteractions = [...this.interactions]; // Existing interactions array
                const index = existingInteractions.findIndex(interaction => interaction.id === newInteractions.id); //matching interaction id to newInteractions id
                if (index !== -1) { //interaction IDs match, replace the whole existing message with the new message
                    const existingInteraction = existingInteractions[index];
                    // Create a new object that contains all properties from newInteractions except messages
                    const updatedInteraction = {
                        ...existingInteraction, // Existing properties
                        ...newInteractions, // Spread newInteractions properties
                        messages: existingInteraction.messages // Keep the original messages for now
                    };
                    const find_msg_index = existingInteractions[index].messages.findIndex(interaction => interaction.id === newInteractions.messages.id); //matching interaction messages id to newInteractions messages id
                    if (find_msg_index !== -1) {
                        const messageIndex = newInteractions.messages.id; // Access the ID of newInteraction.messages
                        if (messageIndex === existingInteractions[index].messages[find_msg_index].id) {
                            // If IDs match, replace the whole existing message with the new message

                            existingInteractions[index].messages[find_msg_index] = {
                                ...newInteractions.messages
                            };
                        }
                    } else {
                        existingInteractions[index].messages.push(newInteractions.messages);
                    }
                    existingInteractions[index] = updatedInteraction;
                } else {
                    // Ensure newInteractions.messages is an array or initialize it as an empty array
                    if (!Array.isArray(newInteractions.messages)) {
                        newInteractions.messages = [newInteractions.messages];
                    }
                    // If the interaction id does not exist, push newInteractions directly
                    existingInteractions.push({
                        ...newInteractions,
                        messages: [...newInteractions.messages] // Ensure messages is properly handled
                    });

                }

                // Now sort the `existingInteractions` array by `time_sent`
                existingInteractions.sort((a, b) => {
                    const timeA = new Date(a.time_sent);
                    const timeB = new Date(b.time_sent);
                    return timeB - timeA; // Sort descending (latest first)
                });

                // Set the sorted array to `this.wb_displayedInteractions`
                this.wb_displayedInteractions = existingInteractions;
                if (!isAdmin && enable_supportagent == 1) {
                    const filteredNewInteractions = existingInteractions.filter(interaction => {
                        const chatagent = interaction.agent;
                        if (chatagent) {
                            const agentIds = Array.isArray(chatagent.agent_id) ? chatagent.agent_id : [chatagent.agent_id];
                            const assignIds = Array.isArray(chatagent.assign_id) ? chatagent.assign_id : [chatagent.assign_id];
                            // Check if `staff_id` is included in either `agentIds` or `assignIds`
                            return agentIds.includes(staff_id) || assignIds.includes(staff_id);
                        }
                        return [];
                    });
                    this.interactions = this.interactions.filter(
                        existing => !filteredNewInteractions.some(newInteraction => newInteraction.id === existing.id)
                    );
                    // Append new interactions to the existing ones
                    this.interactions = [...this.interactions, ...filteredNewInteractions];
                } else {
                    // Append new interactions for admins
                    this.interactions = existingInteractions;
                }
                // Call your existing methods after updating interactions
                this.wb_filterInteractions();
                this.wb_updateSelectedInteraction();
            },
            wb_updateSelectedInteraction() {
                const wb_new_index = this.interactions.findIndex(interaction => interaction.receiver_id === this.wb_selectedinteractionMobNo && interaction.wa_no === this.wb_selectedinteractionSenderNo && interaction.id === this.wb_selectedinteractionId);
                this.wb_selectedinteraction = this.interactions[wb_new_index];
            },
            wb_getTime(timeString) {
                const date = new Date(timeString);
                const hour = date.getHours();
                const minute = date.getMinutes();
                const period = hour < 12 ? 'AM' : 'PM';
                const formattedHour = hour % 12 || 12;
                return `${formattedHour}:${minute < 10 ? '0' + minute : minute} ${period}`;
            },
            getDate(dateString) {
                const wb_date = new Date(dateString);
                const wb_options = {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                };
                return wb_date.toLocaleDateString('en-GB', wb_options).replace(' ', '-').replace(' ', '-');
            },
            wb_shouldShowDate(currentMessage, previousMessage) {
                if (!previousMessage) return true;
                return this.getDate(currentMessage.time_sent) !== this.getDate(previousMessage.time_sent);
            },
            wb_scrollToBottom() {
                this.$nextTick(() => {
                    const wb_chatContainer = this.$refs.wb_chatContainer;
                    if (wb_chatContainer) {
                        wb_chatContainer.scrollTop = wb_chatContainer.scrollHeight;
                    }
                });
            },
            wb_getAvatarInitials(name) {
                const wb_words = name.split(' ');
                const wb_initials = wb_words.slice(0, 2).map(word => word.charAt(0)).join('');
                return wb_initials.toUpperCase();
            },
            playNotificationSound() {
                var enableSound = "<?= get_option('enable_wtc_notification_sound'); ?>";

                if (enableSound == 1) {
                    var audio = new Audio('<?= module_dir_url('whatsbot', 'assets/audio/whatsapp_notification.mp3'); ?>');
                    audio.play();
                }
            },
            wb_countUnreadMessages(interactionId) {
                const interaction = this.interactions ? this.interactions.find(inter => inter.id === interactionId) : undefined;
                if (interaction) {
                    return interaction.messages.filter(message => message.is_read == 0).length;
                }
                return 0;
            },
            async wb_toggleRecording() {
                if (!this.wb_recording) {

                    this.wb_startRecording();
                } else {

                    this.wb_stopRecording();
                }
            },
            wb_startRecording() {
                // Initialize Recorder.js if not already initialized
                if (!this.recorder) {
                    this.recorder = new Recorder({
                        type: "mp3",
                        sampleRate: 16000,
                        bitRate: 16,
                        onProcess: (buffers, powerLevel, bufferDuration, bufferSampleRate) => {

                        }
                    });
                }
                this.recorder.open((stream) => {
                    this.wb_recording = true;
                    this.recorder.start();
                }, (err) => {
                    console.error("Failed to start wb_recording:", err);

                });
            },
            wb_stopRecording() {
                if (this.recorder && this.wb_recording) {
                    this.recorder.stop((blob, duration) => {
                        this.recorder.close();
                        this.wb_recording = false;
                        this.wb_audioBlob = blob;
                        this.wb_sendMessage();
                        this.wb_recordedAudio = URL.createObjectURL(blob);
                    }, (err) => {
                        console.error("Failed to stop wb_recording:", err);

                    });
                }
            },
            wb_handleImageAttachmentChange(event) {
                this.wb_imageAttachment = event.target.files[0];
                this.wb_imagePreview = URL.createObjectURL(this.wb_imageAttachment);
                this.showAttachmentOptions = false;
            },
            wb_handleVideoAttachmentChange(event) {
                this.wb_videoAttachment = event.target.files[0];
                this.wb_videoPreview = URL.createObjectURL(this.wb_videoAttachment);
                this.showAttachmentOptions = false;
            },
            wb_handleDocumentAttachmentChange(event) {
                this.wb_documentAttachment = event.target.files[0];
                this.showAttachmentOptions = false;
            },
            wb_removeImageAttachment() {
                this.wb_imageAttachment = null;
                this.wb_imagePreview = '';
            },
            wb_removeVideoAttachment() {
                this.wb_videoAttachment = null;
                this.wb_videoPreview = '';
            },
            wb_removeDocumentAttachment() {
                this.wb_documentAttachment = null;
            },
            wb_formatTime(timestamp) {
                const currentDate = new Date();
                const messageDate = new Date(timestamp);
                const diffInMs = currentDate - messageDate;
                const diffInHours = diffInMs / (1000 * 60 * 60);

                if (diffInHours < 24) {
                    // Less than 24 hours, display time
                    const hour = messageDate.getHours();
                    const minute = messageDate.getMinutes();
                    const period = hour < 12 ? 'AM' : 'PM';
                    const formattedHour = hour % 12 || 12;
                    return `${formattedHour}:${minute < 10 ? '0' + minute : minute} ${period}`;
                } else {
                    // More than 24 hours, display wb_date in dd-mm-yy format
                    const day = messageDate.getDate();
                    const month = messageDate.getMonth() + 1;
                    const year = messageDate.getFullYear() % 100; // Get last two digits of the year
                    return `${day}-${month < 10 ? '0' + month : month}-${year}`;
                }
            },
            wb_alertTime(lastMsgTime) {
                const timezone = "<?= get_option('default_timezone'); ?>"; // Set the desired timezone
                if (lastMsgTime) {
                    // Parse the last message time in the given timezone
                    const messageDate = new Date(lastMsgTime);
                    // Get the current date and time in the specified timezone
                    const currentDate = new Date(new Date().toLocaleString("en-US", {
                        timeZone: timezone
                    }));
                    const diffInMs = currentDate - messageDate;
                    const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60)); // Round down to nearest hour
                    const diffInMinutes = Math.floor((diffInMs % (1000 * 60 * 60)) / (1000 * 60)); // Calculate remaining minutes
                    // Check if the difference is less than 24 hours
                    if (diffInHours < 24) {
                        // Calculate remaining time within 24 hours
                        const remainingHours = 23 - diffInHours; // Subtract one hour from 24
                        const remainingMinutes = 60 - diffInMinutes;
                        return `Reply within ${remainingHours} hours and ${remainingMinutes} minutes`;
                    } else {
                        return null;
                    }
                } else {
                    return lastMsgTime;
                }
            },
            wb_stripHTMLTags(str) {
                return str ? str.replace(/<\/?[^>]+(>|$)/g, "") : "";
            },
            wb_truncateText(text, charLimit) {
                const strippedText = this.wb_stripHTMLTags(text);
                if (strippedText.length > charLimit) {
                    return strippedText.slice(0, charLimit) + '...';
                }
                return strippedText;
            },
            wb_filterInteractions() {
                let filtered = this.interactions;

                if (this.wb_selectedWaNo !== "*") {
                    filtered = filtered.filter(interaction => interaction.wa_no === this.wb_selectedWaNo);
                }
                this.wb_filteredInteractions = filtered;
                this.wb_searchInteractions(); // Call wb_searchInteractions to apply the search text filter
            },
            wb_searchInteractions() {
                if (this.wb_searchText) {
                    const searchText = this.wb_searchText.toLowerCase();
                    this.wb_displayedInteractions = this.wb_filteredInteractions.filter(interaction => {
                        return Object.keys(interaction).some(key => {
                            const value = interaction[key];
                            return value != null && value.toString().toLowerCase().includes(searchText);
                        });
                    });
                } else {
                    this.wb_displayedInteractions = this.wb_filteredInteractions;
                }
            },
            resetFilters() {
                this.selectedTab = 'searching',
                    this.noResultsMessage = '';
                this.wb_filterInteractions();
            },
            wb_handleAllFilters(e) {
                const selectedRelationType = this.wb_reltype_filter;
                const selectedSource = this.wb_lead_sources; // For leads
                const selectedStatus = this.wb_lead_status; // For leads
                const selectedGroup = this.wb_contact_groups; // For contacts
                const selectedAgent = this.wb_agents_filter; // For agents
                // Start with all filtered interactions
                let filteredInteractions = [...this.wb_filteredInteractions];
                // Filter by relation type
                if (selectedRelationType) {
                    filteredInteractions = filteredInteractions.filter(interaction => {
                        const isMatch = interaction.type === selectedRelationType;
                        return isMatch;
                    });
                }
                // Additional filtering for leads
                if (selectedRelationType === 'leads') {
                    if (selectedSource) {
                        filteredInteractions = filteredInteractions.filter(interaction => {
                            const isMatch = interaction.source === selectedSource;
                            return isMatch;
                        });
                    }
                    if (selectedStatus) {
                        filteredInteractions = filteredInteractions.filter(interaction => {
                            const isMatch = interaction.status === selectedStatus;
                            return isMatch;
                        });
                    }
                }
                // Additional filtering for contacts
                if (selectedRelationType === 'contacts') {
                    if (selectedGroup) {
                        filteredInteractions = filteredInteractions.filter(interaction => {
                            // Check if any group in the array has the matching groupid
                            const hasMatchingGroup = interaction.group && Array.isArray(interaction.group) &&
                                interaction.group.some(group => group.groupid === selectedGroup);
                            return hasMatchingGroup;
                        });
                    }
                }
                // Apply agent filtering overall if selectedAgent exists
                if (selectedAgent) {
                    filteredInteractions = filteredInteractions.filter(interaction => {
                        // Check if agent_id exists and is an array
                        const isMatch = interaction.agent && Array.isArray(interaction.agent.agent_id) &&
                            interaction.agent.agent_id.includes(selectedAgent);
                        return isMatch;
                    });
                }
                // Assign filtered interactions to displayed interactions
                this.wb_displayedInteractions = filteredInteractions;

                if (this.wb_displayedInteractions.length === 0) {
                    this.noResultsMessage = `<div class="flex justify-center items-center"><p class="text-gray-500 text-lg font-medium">No chat's found for the selected filters.</p></div>`;
                } else {
                    this.noResultsMessage = '';
                }
            },

            wb_handleItemClick(menu, submenu = null) {
                const input_msg = this.wb_newMessage;
                this.isLoading = true;
                $.ajax({
                    url: '<?= site_url('whatsbot/ai_response'); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        menu: menu,
                        submenu: submenu,
                        input_msg: input_msg,
                    },
                    success: (response) => {
                        if (response.status === false) {
                            alert_float('danger', response.message);
                        } else {
                            this.wb_newMessage = response.message || input_msg;
                            this.$nextTick(() => {
                                const input = this.$refs.inputField;
                                input.focus();
                            });
                        }
                        this.isLoading = false;
                    },
                })
            },
            ucfirst(str) {
                return str.charAt(0).toUpperCase() + str.slice(1);
            },
            toggleEmojiPicker() {
                this.wb_showEmojiPicker = !this.wb_showEmojiPicker;
                if (this.wb_showEmojiPicker) {
                    this.initEmojiPicker();
                } else {
                    this.removeEmojiPicker();
                }
            },
            initEmojiPicker() {
                const container = document.getElementById('emoji-picker-container');
                container.innerHTML = '';
                const pickerOptions = {
                    onEmojiSelect: (emoji) => {
                        this.wb_newMessage += emoji.native;
                    }
                };
                const picker = new EmojiMart.Picker(pickerOptions);
                container.appendChild(picker);
                const input = document.getElementById('wb_newMessage');
                const rect = input.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                document.addEventListener('click', this.handleClickOutside);
            },
            removeEmojiPicker() {
                const container = this.$refs.emojiPickerContainer;
                if (container) {
                    container.innerHTML = '';
                }
                document.removeEventListener('click', this.handleClickOutside);
            },
            handleClickOutside(event) {
                const emojiContainer = this.$refs.emojiPickerContainer;
                if (
                    (emojiContainer && !emojiContainer.contains(event.target) && !event.target.closest('#emoji_btn'))
                ) {
                    this.wb_showEmojiPicker = false;
                    this.removeEmojiPicker();
                    document.removeEventListener('click', this.handleClickOutside);
                }
            },
            toggleAttachmentOptions() {
                this.showAttachmentOptions = !this.showAttachmentOptions;
            },
        },

        watch: {
            wb_searchText() {
                this.wb_searchInteractions();
            },
            wb_displayedInteractions(newInteractions) {
                newInteractions.forEach(interaction => {
                    const previousCount = this.previousCounts[interaction.id] || 0;
                    const currentCount = this.wb_countUnreadMessages(interaction.id);

                    if (currentCount > previousCount) {
                        this.playNotificationSound();
                    }
                    this.$set(this.previousCounts, interaction.id, currentCount);
                });
            }
        },
        mounted() {
            // Add event listener to hide the dropdown when clicking outside
            window.addEventListener('mouseup', (event) => {
                var pol = document.getElementById('all_atech');
                var canned = document.getElementById('canned_replies');
                if (pol) {
                    // Check if the clicked element is outside the attachment options and the button
                    if (event.target !== pol && !pol.contains(event.target) && !event.target.closest('button')) {
                        this.showAttachmentOptions = false; // Hide the dropdown
                    }
                }
                if (canned) {
                    // Check if the clicked element is outside the attachment options and the button
                    if (event.target !== canned && !canned.contains(event.target) && !event.target.closest('button')) {
                        this.cannedRepliesVisible = false; // Hide the dropdown
                    }
                }
            });
        },
        created() {
            this.wb_fetchinteractions().then(() => {
                // After fetching data, initialize Pusher to listen for updates
                this.initializePusher();
                this.handlerequiredata();
            });
            this.darkMode = localStorage.getItem('darkMode') === 'true';
        },
        computed: {
            overdueAlert() {
                const lastMsgTime = this.wb_selectedinteraction.last_msg_time;
                const timezone = "<?= get_option('default_timezone'); ?>";
                if (lastMsgTime) {
                    const currentDate = new Date(new Date().toLocaleString("en-US", {
                        timeZone: timezone
                    }));
                    const messageDate = new Date(lastMsgTime);
                    const diffInHours = Math.floor((currentDate - messageDate) / (1000 * 60 * 60));

                    if (diffInHours >= 24) {
                        return `
							<div class="flex items-center bg-amber-100 dark:bg-gray-700 dark:text-yellow-400 w-full text-amber-700 p-2 rounded relative " role="alert">
							<i class="fas fa-exclamation-triangle mr-2 fa-xl text-amber-700 dark:text-yellow-400"></i>
							<span class="block sm:inline"><span class="font-semibold text-amber-700 dark:text-yellow-400">24 hours limit</span> WhatsApp blocks messages 24 hours after the last contact, but template messages can still be sent.</span>
						</div>`;
                    }
                }
                return null;
            },
            wb_selectedInteraction() {
                return this.wb_selectedinteractionIndex !== null ? this.interactions[this.wb_selectedinteractionIndex] : null;
            },
            wb_showSendButton() {
                return this.wb_imageAttachment || this.wb_videoAttachment || this.wb_documentAttachment || this.wb_newMessage.trim();
            },

            isButtonEnabled() {
                return this.wb_newMessage.trim().length > 0;
            },
            wb_uniqueWaNos() {
                // Create a Set to store unique wa_no values
                const wb_uniqueWaNos = new Set();
                // Filter out interactions with duplicate wa_no values
                return this.interactions.filter(interaction => {
                    if (!wb_uniqueWaNos.has(interaction.wa_no)) {
                        wb_uniqueWaNos.add(interaction.wa_no);

                        return true;
                    }
                    return false;
                });
            },
            filteredLanguages() {
                return this.languages.filter(lang => lang.toLowerCase().includes(this.searchQuery.toLowerCase()));
            },
        },
    });
</script>
