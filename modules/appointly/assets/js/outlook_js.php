<script type="text/javascript" src="https://alcdn.msauth.net/lib/1.3.4/js/msal.js"></script>
<script>
     var outlookConf = {
          msalConfig: {
               auth: {
                    clientId: "<?= get_option('appointly_outlook_client_id') ?>",
                    authority: "https://login.microsoftonline.com/common",
                    redirectUri: admin_url + 'appointly/appointments',
               },
               cache: {
                    cacheLocation: "localStorage",
                    storeAuthStateInCookie: true
               },
               system: {
                    loadFrameTimeout: 50000
               }
          },
          graphConfig: {
               graphMeEndpoint: "https://graph.microsoft.com/v1.0/me"
          },
          requestObj: {
               scopes: ["openid", "User.ReadWrite", "Calendars.ReadWrite"]
          }
     }
     var myMSALObj = new Msal.UserAgentApplication(outlookConf.msalConfig);
     // Register Callbacks for redirect flow
     myMSALObj.handleRedirectCallback(authRedirectCallBack);

     function signInToOutlook(event) {
          // Prevent default action if called from a link
          if (event && event.preventDefault) {
               event.preventDefault();
          }

          console.log('Signing in to Outlook...');

          myMSALObj.loginPopup(outlookConf.requestObj).then(function(loginResponse) {
               if (loginResponse.fromCache === false) {
                    window.location.reload();
               }
               //Successful login
               checkOutlookAuthentication();
               //Call MS Graph using the token in the response
               // acquireTokenPopupAndCallMSGraph();
          }).catch(function(error) {
               renderErrorToConsole(error);
          });
     }

     function outlookSignOut(event) {
          // Prevent default action if called from a link
          if (event && event.preventDefault) {
               event.preventDefault();
          }

          console.log('Signing out from Outlook...');

          // First, clear MSAL cache
          myMSALObj.clearCache();

          // Properly logout from MSAL
          myMSALObj.logout({
               onRedirectNavigate: (url) => {
                    // Return false to stop navigation after local logout
                    return false;
               }
          });

          // Clear localStorage
          Object.keys(localStorage).forEach(key => {
               if (key.startsWith('msal.') || key.includes('outlook')) {
                    localStorage.removeItem(key);
               }
          });

          // Clear session storage
          Object.keys(sessionStorage).forEach(key => {
               if (key.startsWith('msal.') || key.includes('outlook')) {
                    sessionStorage.removeItem(key);
               }
          });

          // Clear cookies
          var msalCookies = document.cookie.split(/=[^;]*(?:;\s*|$)/);
          for (var i = 0; i < msalCookies.length; i++) {
               if (/^msal/.test(msalCookies[i])) {
                    document.cookie = msalCookies[i] + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
               }
          }

          // Update UI
          var loginButton = document.getElementById('sign_in_outlook');
          if (loginButton) {
               loginButton.innerHTML = '<i class="fa-regular fa-envelope" aria-hidden="true"><?= _l('appointment_connect') ?>';
               loginButton.title = "<?= _l('appointment_connect') ?>";
               loginButton.removeEventListener('click', outlookSignOut);
               loginButton.addEventListener('click', signInToOutlook);
          }

          // Reload page to reset state
          window.location.reload();
     }

     function acquireTokenPopupAndCallMSGraph() {
          //Always start with acquireTokenSilent to obtain a token in the signed in user from cache
          myMSALObj.acquireTokenSilent(outlookConf.requestObj).then(function(tokenResponse) {
               callMSGraph(outlookConf.graphConfig.graphMeEndpoint, tokenResponse.accessToken);
               // getOutlookEvents("https://graph.microsoft.com/v1.0/me/calendar/events", tokenResponse.accessToken);
          }).catch(function(error) {
               renderErrorToConsole(error);
               // Upon acquireTokenSilent failure (due to consent or interaction or login required ONLY)
               // Call acquireTokenPopup(popup window)
               if (requiresInteraction(error.errorCode)) {
                    document.getElementById('sign_in_outlook').innerHTML = "<?= _l('appointment_login_to_outlook') ?>";
                    myMSALObj.acquireTokenPopup(outlookConf.requestObj).then(function(tokenResponse) {
                         callMSGraph(outlookConf.graphConfig.graphMeEndpoint, tokenResponse.accessToken);
                         // getOutlookEvents("https://graph.microsoft.com/v1.0/me/calendar/events", tokenResponse.accessToken);
                    }).catch(function(error) {
                         renderErrorToConsole(error);
                    });
               }
          });
     }

     function callMSGraph(url, accessToken) {
          var msAccessToken = document.getElementById('ms-access-token');
          if (msAccessToken !== null) {
               msAccessToken.value = accessToken;
          } else {
               console.log('ms-access-token element not found, but proceeding with API call');
          }

          var xmlHttp = new XMLHttpRequest();
          /**
           * True for asynchronous
           */
          xmlHttp.open("GET", url, true);
          xmlHttp.setRequestHeader('Authorization', 'bearer ' + accessToken);
          xmlHttp.send();
     }

     function checkOutlookAuthentication() {
          var loginButton = document.getElementById('sign_in_outlook');

          // If the button doesn't exist, just return without trying to modify it
          if (!loginButton) {
               console.log('Outlook login button not found on this page');
               return;
          }

          // Check if the user is authenticated with Outlook
          if (myMSALObj.getAccount()) {
               loginButton.innerHTML = '<i class="fa-solid fa-check-circle tw-text-success-600 tw-mr-1"></i> <?= _l('appointment_connected'); ?>';
               loginButton.classList.remove('tw-text-primary-600');
               loginButton.classList.add('tw-text-success-600');
               loginButton.title = "<?= _l('appointments_outlook_revoke') ?>";

               // Remove any existing event listeners by cloning and replacing
               var newLoginButton = loginButton.cloneNode(true);
               loginButton.parentNode.replaceChild(newLoginButton, loginButton);
               loginButton = newLoginButton;

               // Create a new function for handling click with confirmation
               loginButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm("<?= _l('appointments_outlook_revoke_confirm') ?>")) {
                         outlookSignOut(e);
                    }
               });
          } else {
               loginButton.innerHTML = '<?= _l('appointment_connect'); ?> <i class="fa fa-arrow-right tw-ml-1"></i>';
               loginButton.classList.remove('tw-text-success-600');
               loginButton.classList.add('tw-text-primary-600');
               loginButton.title = "<?= _l('appointment_connect') ?>";

               // Remove any existing event listeners by cloning and replacing
               var newLoginButton = loginButton.cloneNode(true);
               loginButton.parentNode.replaceChild(newLoginButton, loginButton);
               loginButton = newLoginButton;

               // Add click event for sign in
               loginButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    signInToOutlook();
               });
          }
     }

     function authRedirectCallBack(error, response) {
          if (error) {
               console.error('Outlook auth redirect error:', error);
               renderErrorToConsole(error);
          } else {
               if (response && response.tokenType === "access_token") {
                    console.log('Got access token from redirect');

                    // Make sure we're being safe about updating DOM elements
                    var msAccessToken = document.getElementById('ms-access-token');
                    if (msAccessToken) {
                         msAccessToken.value = response.accessToken;
                    }

                    callMSGraph(outlookConf.graphConfig.graphMeEndpoint, response.accessToken);
               }
          }
     }

     function requiresInteraction(errorCode) {
          if (!errorCode || !errorCode.length) {
               return false;
          }
          return errorCode === "consent_required" ||
               errorCode === "interaction_required" ||
               errorCode === "login_required";
     }

     async function outlookAddOrUpdateEvent(data) {
          if (!isOutlookLoggedIn()) {
               return false;
          }

          var accessToken = document.getElementById('ms-access-token').value;
          var eventId = document.getElementById('ms-outlook-event-id').value;

          var paramData = [];
          var outlook_attendees = [];
          var normal_date = '';
          var event = '';
          var attendees_data = '';

          data.map(function(form) {
               if (form['name'] == 'attendees[]') {
                    outlook_attendees.push(form['value'])
               }
               paramData[form['name']] = form['value'];
          });

          paramData.attendees = [outlook_attendees];

          delete paramData['attendees[]'];

          var attendeeDataPromisse = new Promise(function(resolve, reject) {
               return resolve($.post(site_url + 'appointly/appointments/getAttendeeData', {
                    ids: Object.values(outlook_attendees)
               }));
          })

          attendeeDataPromisse.then(function(attendees) {
               // selected contact to attendee list
               if ($('body').find('#div_email input').val().length > 0) {
                    var clientContactLead = {
                         emailAddress: {
                              address: $('body').find('#div_email input').val(),
                              name: $('body').find('#div_name input').val(),
                         }
                    }
                    attendees.push(clientContactLead);
               }
               return attendees;
          });

          var attendees_data = await attendeeDataPromisse;

          paramData.date = moment(paramData.date, ['DD/MM/YYYY HH:mm:ss', 'DD.MM.YYYY HH:mm:ss', 'MM-DD-YYYY HH:mm:ss', 'MM.DD.YYYY HH:mm:ss', 'MM/DD/YYYY HH:mm:ss', 'YYYY-MM-DD HH:mm:ss']).format();

          event = {
               subject: paramData.subject,
               body: {
                    contentType: "HTML",
                    content: paramData.description
               },
               start: {
                    dateTime: paramData.date,
                    timeZone: app.options.timezone
               },
               end: {
                    dateTime: paramData.date,
                    timeZone: app.options.timezone
               },
               location: {
                    displayName: (paramData.address) ? paramData.address : ''
               },
               attendees: attendees_data,
               Importance: "Normal",
               HasAttachments: false,
               isReminderOn: true,
               reminderMinutesBeforeStart: <?= get_option('appointly_google_meet_reminder_minutes') ?: 30 ?>
          };

          var url = 'https://graph.microsoft.com/v1.0/me/events/';
          var requestType = 'POST';

          // used for updating event
          if (typeof eventId != 'undefined' && eventId != '') {
               requestType = 'PATCH'
               url += eventId;
          }

          var dfd = jQuery.Deferred();
          var promise = dfd.promise();

          $.ajax({
               url,
               type: requestType,
               headers: {
                    'Content-Type': 'application/json;charset=UTF-8',
                    'Authorization': 'Bearer ' + accessToken,
               },
               data: JSON.stringify(event)
          }).done(function(appointment) {
               if (appointment.id) {
                    $.post(site_url + 'appointly/appointments/newOutlookEvent', {
                         outlook_event_id: appointment.id,
                         outlook_calendar_link: appointment.webLink,
                    }).done(function(r) {
                         dfd.resolve();
                         if (r.result) {
                              if (requestType == 'POST') {
                                   alert_float('success', "<?= _l('appointment_created') ?>");
                              } else {
                                   alert_float('success', "<?= _l('appointment_updated') ?>");
                              }

                              // Instead of reloading the page, refresh the table and close the modal
                              if ($.fn.DataTable.isDataTable('.table-appointments')) {
                                   $('.table-appointments').DataTable().ajax.reload(null, false);
                              }
                              $('.modal').modal('hide');
                         }
                    });
               }
          }).fail(function(error) {
               renderErrorToConsole(error);
          });
          promise.then(function() {
               return dfd.promise();
          })
     }

     function addToOutlookNewEventFromUpdate(data) {
          // Get the appointment ID from the form or parameter
          var appointment_id;

          // Handle both when data is the ID directly (from button click)
          // or when it's form data (from form submission)
          if (typeof data === 'number' || typeof data === 'string') {
               appointment_id = data;
          } else {
               appointment_id = $('input[name="appointment_id"]').val();
          }

          if (!isOutlookLoggedIn()) {
               signInToOutlook();
               return false;
          }

          // Get fresh token
          myMSALObj.acquireTokenSilent(outlookConf.requestObj)
               .then(function(tokenResponse) {
                    var accessToken = tokenResponse.accessToken;
                    var outlook_attendees = [];
                    if (Array.isArray(data)) {
                         data.forEach(function(form) {
                              if (form['name'] == 'attendees[]') {
                                   outlook_attendees.push(form['value']);
                              }
                         });
                    } else {
                         // For update.php (direct form selector)
                         var attendeesSelect = $('select[name="attendees[]"]');
                         if (attendeesSelect.length) {
                              outlook_attendees = attendeesSelect.val() || [];
                         }
                    }

                    // Get attendee data if we have staff IDs to fetch
                    if (outlook_attendees.length > 0) {
                         $.post(site_url + 'appointly/appointments/getAttendeeData', {
                              ids: outlook_attendees,
                              <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                         }).done(function(attendees) {
                              console.log('Raw attendees response:', attendees);
                              console.log('Type of attendees:', typeof attendees);

                              try {
                                   if (typeof attendees === 'string') {
                                        attendees = JSON.parse(attendees);
                                   }
                              } catch (e) {
                                   console.log('JSON parse error:', e);
                                   attendees = [];
                              }

                              console.log('Parsed attendees:', attendees);
                              console.log('Is array?', Array.isArray(attendees));

                              // Ensure attendees is always an array
                              if (!Array.isArray(attendees)) {
                                   console.log('Converting to array, current type:', typeof attendees);
                                   attendees = [];
                              }

                              // Add the provider (service provider) to attendees
                              var provider_id = $('select[name="provider_id"]').val();
                              console.log('Provider ID:', provider_id);

                              if (provider_id) {
                                   // Get provider details via AJAX
                                   $.post(site_url + 'appointly/appointments/getAttendeeData', {
                                        ids: [provider_id],
                                        <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                                   }).done(function(providerData) {
                                        console.log('Provider data received:', providerData);
                                        try {
                                             if (typeof providerData === 'string') {
                                                  providerData = JSON.parse(providerData);
                                             }
                                             if (Array.isArray(providerData) && providerData.length > 0) {
                                                  attendees = attendees.concat(providerData);
                                             }
                                        } catch (e) {
                                             console.log('Error parsing provider data:', e);
                                        }

                                        // Add related contact based on appointment type
                                        addRelatedContactAndCreateEvent(attendees);
                                   }).fail(function() {
                                        console.log('Failed to get provider data');
                                        // Still try to add related contact
                                        addRelatedContactAndCreateEvent(attendees);
                                   });
                              } else {
                                   // No provider, just add related contact
                                   addRelatedContactAndCreateEvent(attendees);
                              }

                              function addRelatedContactAndCreateEvent(attendees) {
                                   // Add external contact if appointment is external
                                   var rel_type = $('select[name="rel_type"]').val();
                                   console.log('Appointment type:', rel_type);

                                   if (rel_type === 'external') {
                                        var external_name = $('input[name="name"]').val();
                                        var external_email = $('input[name="email"]').val();
                                        console.log('External contact:', external_name, external_email);

                                        if (external_name && external_email) {
                                             attendees.push({
                                                  emailAddress: {
                                                       address: external_email,
                                                       name: external_name
                                                  },
                                                  type: "required"
                                             });
                                        }
                                   } else if (rel_type === 'internal') {
                                        // For internal appointments, check if there's a selected contact
                                        var contact_select = $('select[name="contact_id"]');
                                        var contact_id = contact_select.val();

                                        if (contact_id) {
                                             var selectedOption = contact_select.find('option:selected');
                                             var contactText = selectedOption.text();

                                             // Fetch contact email via AJAX
                                             $.post(site_url + 'appointly/appointments/getContactEmail', {
                                                  contact_id: contact_id,
                                                  <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                                             }).done(function(contactEmailData) {
                                                  if (typeof contactEmailData === 'string') {
                                                       contactEmailData = JSON.parse(contactEmailData);
                                                  }

                                                  if (contactEmailData && contactEmailData.email) {
                                                       attendees.push({
                                                            emailAddress: {
                                                                 address: contactEmailData.email,
                                                                 name: contactEmailData.name || contactText.split(' - ')[0]
                                                            },
                                                            type: "required"
                                                       });
                                                  }

                                                  // Continue with event creation after trying to add contact
                                                  console.log('Final attendees array before creating event:', attendees);
                                                  createOutlookEvent(attendees);
                                             }).fail(function(xhr, status, error) {
                                                  console.log('Failed to get contact email:', status, error);

                                                  // Continue without contact email
                                                  console.log('Final attendees array before creating event:', attendees);
                                                  createOutlookEvent(attendees);
                                             });

                                             return; // Exit here, createOutlookEvent will be called from AJAX callback
                                        }
                                   }

                                   console.log('Final attendees array before creating event:', attendees);
                                   createOutlookEvent(attendees);
                              }
                         }).fail(function(xhr, status, error) {
                              console.log('Failed to get attendee data:', status, error);
                              console.log('XHR response:', xhr.responseText);
                              createOutlookEvent([]);
                         });
                    } else {
                         console.log('No staff attendees selected, but will add provider and related contact');

                         // Start with empty array
                         var attendees = [];

                         // 1. Add the provider (service provider) to attendees
                         var provider_id = $('select[name="provider_id"]').val();
                         console.log('Provider ID:', provider_id);

                         if (provider_id) {
                              // Get provider details via AJAX
                              $.post(site_url + 'appointly/appointments/getAttendeeData', {
                                   ids: [provider_id],
                                   <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                              }).done(function(providerData) {
                                   console.log('Provider data received:', providerData);
                                   try {
                                        if (typeof providerData === 'string') {
                                             providerData = JSON.parse(providerData);
                                        }
                                        if (Array.isArray(providerData) && providerData.length > 0) {
                                             attendees = attendees.concat(providerData);
                                        }
                                   } catch (e) {
                                        console.log('Error parsing provider data:', e);
                                   }

                                   // 2. Add related contact based on appointment type
                                   addRelatedContactAndCreateEvent(attendees);
                              }).fail(function() {
                                   console.log('Failed to get provider data');
                                   // Still try to add related contact
                                   addRelatedContactAndCreateEvent(attendees);
                              });
                         } else {
                              // No provider, just add related contact
                              addRelatedContactAndCreateEvent(attendees);
                         }

                         function addRelatedContactAndCreateEvent(attendees) {
                              var rel_type = $('select[name="rel_type"]').val();

                              if (rel_type === 'external') {
                                   var external_name = $('input[name="name"]').val();
                                   var external_email = $('input[name="email"]').val();

                                   if (external_name && external_email) {
                                        attendees.push({
                                             emailAddress: {
                                                  address: external_email,
                                                  name: external_name
                                             },
                                             type: "required"
                                        });
                                   }
                              } else if (rel_type === 'lead_related') {
                                   var contact_select = $('select[name="contact_id"]');
                                   var contact_id = contact_select.val();

                                   if (contact_id) {
                                        var selectedOption = contact_select.find('option:selected');
                                        var contactText = selectedOption.text();
                                   }
                              } else if (rel_type === 'internal') {

                                   // For internal appointments, check if there's a selected contact
                                   var contact_select = $('select[name="contact_id"]');
                                   var contact_id = contact_select.val();

                                   if (contact_id) {
                                        var selectedOption = contact_select.find('option:selected');
                                        var contactText = selectedOption.text();

                                        // Fetch contact email via AJAX
                                        $.post(site_url + 'appointly/appointments/getContactEmail', {
                                             contact_id: contact_id,
                                             <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                                        }).done(function(contactEmailData) {
                                             if (typeof contactEmailData === 'string') {
                                                  contactEmailData = JSON.parse(contactEmailData);
                                             }

                                             if (contactEmailData && contactEmailData.email) {
                                                  attendees.push({
                                                       emailAddress: {
                                                            address: contactEmailData.email,
                                                            name: contactEmailData.name || contactText.split(' - ')[0]
                                                       },
                                                       type: "required"
                                                  });
                                             }

                                             // Continue with event creation after trying to add contact
                                             createOutlookEvent(attendees);
                                        }).fail(function(xhr, status, error) {
                                             console.log('Failed to get contact email:', status, error);

                                             // Continue without contact email
                                             createOutlookEvent(attendees);
                                        });

                                        return; // Exit here, createOutlookEvent will be called from AJAX callback
                                   }
                              }

                              createOutlookEvent(attendees);
                         }
                    }

                    function createOutlookEvent(attendees) {
                         // Get date based on form type
                         var date;

                         if (Array.isArray(data)) {
                              date = data.find(item => item.name === 'date')?.value;
                         } else {
                              date = $('input[name="date"]').val();
                         }

                         var formattedDate = moment(date, [
                              'DD/MM/YYYY HH:mm',
                              'DD.MM.YYYY HH:mm',
                              'MM-DD-YYYY HH:mm',
                              'MM.DD.YYYY HH:mm',
                              'MM/DD/YYYY HH:mm',
                              'YYYY-MM-DD HH:mm'
                         ]).format();

                         // Create event object
                         var event = {
                              subject: Array.isArray(data) ?
                                   (data.find(item => item.name === 'subject')?.value || '') : $('input[name="subject"]').val(),
                              body: {
                                   contentType: "HTML",
                                   content: Array.isArray(data) ?
                                        (data.find(item => item.name === 'description')?.value || '') : $('textarea[name="description"]').val()
                              },
                              start: {
                                   dateTime: formattedDate,
                                   timeZone: app.options.timezone
                              },
                              end: {
                                   dateTime: moment(formattedDate).add(1, 'hour').format(),
                                   timeZone: app.options.timezone
                              },
                              location: {
                                   displayName: Array.isArray(data) ?
                                        (data.find(item => item.name === 'address')?.value || '') : $('input[name="address"]').val()
                              },
                              attendees: attendees,
                              isReminderOn: true,
                              reminderMinutesBeforeStart: <?= get_option('appointly_google_meet_reminder_minutes') ?: 30 ?>
                         };

                         // Create event in Outlook
                         $.ajax({
                              url: 'https://graph.microsoft.com/v1.0/me/events',
                              type: 'POST',
                              headers: {
                                   'Content-Type': 'application/json',
                                   'Authorization': 'Bearer ' + accessToken,
                              },
                              data: JSON.stringify(event),
                              beforeSend: function() {
                                   $('#addToOutlookBtn').prop('disabled', true)
                                        .html('<i class="fa fa-refresh fa-spin fa-fw"></i> <?= _l("appointment_calendar_adding_to_outlook") ?>');
                              }
                         }).done(function(response) {
                              if (response.id) {
                                   $.post(site_url + 'appointly/appointments/save_outlook_event_id', {
                                        appointment_id: appointment_id,
                                        outlook_event_id: response.id,
                                        outlook_calendar_link: response.webLink || '',
                                        <?= $this->security->get_csrf_token_name() ?>: '<?= $this->security->get_csrf_hash() ?>'
                                   }).done(function(response) {
                                        alert_float('success', "<?= _l('appointment_added_to_outlook') ?>");
                                        window.location.reload();
                                        $('.modal').modal('hide');

                                   }).fail(function(xhr, status, error) {
                                        alert_float('danger', "<?= _l('appointment_outlook_event_save_failed') ?>");
                                        $('#addToOutlookBtn').prop('disabled', false)
                                             .html("<?= _l('appointment_add_to_outlook') ?> <i class=\"fa fa-envelope\"></i>");
                                   });
                              }
                         }).fail(function(xhr) {
                              if (xhr.status === 401) {
                                   myMSALObj.acquireTokenPopup(outlookConf.requestObj)
                                        .then(function(newTokenResponse) {
                                             accessToken = newTokenResponse.accessToken;
                                             addToOutlookNewEventFromUpdate(data);
                                        })
                                        .catch(function(error) {
                                             alert_float('danger', "<?= _l('appointment_outlook_auth_error') ?>");
                                        });
                              } else {
                                   alert_float('danger', "<?= _l('appointment_outlook_error') ?>");
                              }
                              $('#addToOutlookBtn').prop('disabled', false)
                                   .html("<?= _l('appointment_add_to_outlook') ?> <i class=\"fa fa-envelope\"></i>");
                         });
                    }
               })
               .catch(function(error) {
                    if (requiresInteraction(error.errorCode)) {
                         myMSALObj.acquireTokenPopup(outlookConf.requestObj)
                              .then(function(tokenResponse) {
                                   addToOutlookNewEventFromUpdate(data);
                              })
                              .catch(function(error) {
                                   alert_float('danger', "<?= _l('appointment_outlook_auth_error') ?>");
                              });
                    }
               });
     }

     function deleteOutlookEvent(id, showAlert = true) {
          if (!id) {
               return false;
          }

          if (isOutlookLoggedIn()) {
               myMSALObj.acquireTokenSilent(outlookConf.requestObj).then(function(Response) {
                    if (Response.accessToken) {
                         $.ajax({
                                   url: 'https://graph.microsoft.com/v1.0/me/events/' + id,
                                   type: 'DELETE',
                                   beforeSend: function(xhr) {
                                        xhr.setRequestHeader('Authorization', 'Bearer ' + Response.accessToken);
                                   }
                              }).done(function() {
                                   if (showAlert) {
                                        alert_float('success', "<?= _l('appointment_outlook_event_deleted') ?>");
                                   }
                              })
                              .fail(function(error) {
                                   renderErrorToConsole(error);
                                   if (showAlert) {
                                        alert_float('danger', "<?= _l('appointment_outlook_event_delete_failed') ?>");
                                   }
                              });
                    } else {
                         return false;
                    }
               }).catch(function(error) {
                    renderErrorToConsole(error);
                    return false;
               });
          } else {
               return false;
          }
     }

     /**
      * Fetch outlook events
      */
     function getOutlookEvents(url, token) {
          $.ajax({
                    url,
                    type: 'GET',
                    beforeSend: function(xhr) {
                         xhr.setRequestHeader('Authorization', 'Bearer ' + token);
                    },
                    data: {}
               }).done(function() {
                    // done
               })
               .fail(function(error) {
                    renderErrorToConsole(error);
               });
     }

     /**
      * Error Log renderer
      */
     function renderErrorToConsole(error) {
          console.log(error)
     }

     // Browser check variables
     var ua = window.navigator.userAgent;
     var msie = ua.indexOf('MSIE ');
     var msie11 = ua.indexOf('Trident/');
     var msedge = ua.indexOf('Edge/');
     var isIE = msie > 0 || msie11 > 0;
     var isEdge = msedge > 0;

     //If you support IE, our recommendation is that you sign-in using Redirect APIs
     //If you as a developer are testing using Edge InPrivate mode, please add "isEdge" to the if check

     // can change this to default an experience outside browser use
     var loginType = isIE ? "REDIRECT" : "POPUP";

     // Initialize button handlers when the document is ready
     document.addEventListener('DOMContentLoaded', function() {
          // Find the sign in button
          var signInButton = document.getElementById('sign_in_outlook');

          // Only set up event handlers if the button exists
          if (signInButton) {
               // Call the authentication check which will set up the event handlers
               checkOutlookAuthentication();
          }
     });

     // runs on page load, change config to try different login types to see what is best for your application
     if (loginType === 'POPUP') {
          // Initialization is now handled by the DOMContentLoaded event listener above
     } else if (loginType === 'REDIRECT') {
          // avoid duplicate code execution on page load in case of iframe and popup window.
          if (myMSALObj.getAccount() && !myMSALObj.isCallback(window.location.hash)) {
               // The UI will be updated by the DOMContentLoaded event listener
          }
     } else {
          console.error('Please set a valid login type');
     }

     /**
      * Check if user is logged in to Outlook
      * @return {boolean} True if logged in, false otherwise
      */
     function isOutlookLoggedIn() {
          // Check if the user has an account in MSAL
          if (typeof myMSALObj !== "undefined" && myMSALObj.getAccount()) {
               return true;
          }

          // Check if we have an access token in the DOM
          var msAccessToken = document.getElementById('ms-access-token');
          if (msAccessToken && msAccessToken.value) {
               return true;
          }

          return false;
     }
</script>
