<?php if (isset($_GET['contact_id'])) { ?>
    <?php 
        $contact =  flexiblekit_get_customer_contact($_GET['contact_id']);
        $contact_type = FLEXIBLEKIT_CUSTOMER_CONTACT_TYPE;
    ?>

    <?php echo flexiblekit_get_kit_content($contact, $contact_type) ?>
<?php }else{ ?>
    <div class="panel_s">
        <div class="panel-body table-responsive">
            <div class="panel-table-full">
                <table id="contacts-table" class="table dt-table">
                    <thead>
                        <tr>
                            <th>
                                <?php
                                echo _l('flexiblekit_name');
                                ?>
                            </th>
                            <th>
                                <?php
                                echo _l('flexiblekit_email');
                                ?>
                            </th>
                            <th>
                                <?php
                                echo _l('flexiblekit_position');
                                ?>
                            </th>
                            <th>
                                <?php echo _l('flexiblekit_options'); ?>
                            </th>
                        </tr>
                    </thead>
                    <tbody >
                        <?php if (count($contacts) > 0) { ?>
                            <?php foreach ($contacts as $contact) { ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo contact_profile_image_url($contact['id']) ?>" class="client-profile-image-small mright5">
                                        <?php echo $contact['firstname'] . ' ' . $contact['lastname'] ?>
                                    </td>
                                    <td>
                                        <?php echo $contact['email'] ?>
                                    </td>
                                    <td>
                                        <?php echo $contact['title'] ?>
                                    </td>
                                    <td>
                                        <a
                                            href="<?php echo flexiblekit_get_customer_url($group, $contact['id']) ?>"
                                            class="btn btn-primary tw-mt-px">
                                            <?php echo _l('flexiblekit') ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php } ?>
<!-- <div class="inline-block new-contact-wrapper" data-title="<?php //echo _l('customer_contact_person_only_one_allowed'); ?>" data-toggle="tooltip"> -->
<!-- </div> -->
<!-- <h5>Because a customer is usually a company that many CONTACT, We will need to list all the CONTACT on this customer as a dropdown so during schedule to target the right customer.</h5> -->