@extends('layouts.admin')

@section('title', 'API Endpoints Reference')

@section('page-title', 'API Endpoints Reference')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title mb-0">
            <i class="bi bi-list-ul"></i> Complete API Endpoints Reference
        </h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th>Endpoint</th>
                                <th>Description</th>
                                <th>Auth Required</th>
                                <th>Parameters</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Authentication Endpoints -->
                            <tr class="table-info">
                                <td colspan="5"><strong>Authentication</strong></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">POST</span></td>
                                <td><code>/api/auth/register</code></td>
                                <td>Register new user</td>
                                <td><i class="bi bi-x text-danger"></i></td>
                                <td>name, email, phone_number, country_code, password</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">POST</span></td>
                                <td><code>/api/auth/login</code></td>
                                <td>User login</td>
                                <td><i class="bi bi-x text-danger"></i></td>
                                <td>email, password</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-danger">POST</span></td>
                                <td><code>/api/auth/logout</code></td>
                                <td>User logout</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>None</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">GET</span></td>
                                <td><code>/api/auth/profile</code></td>
                                <td>Get user profile</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>None</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">PUT</span></td>
                                <td><code>/api/auth/profile</code></td>
                                <td>Update user profile</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>name, about, privacy settings</td>
                            </tr>

                            <!-- Chat Endpoints -->
                            <tr class="table-info">
                                <td colspan="5"><strong>Chats</strong></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">GET</span></td>
                                <td><code>/api/chats</code></td>
                                <td>List user chats</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>page, per_page, search, type</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">POST</span></td>
                                <td><code>/api/chats</code></td>
                                <td>Create new chat</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>type, name, description, participant_ids</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">GET</span></td>
                                <td><code>/api/chats/{id}</code></td>
                                <td>Get chat details</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>None</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">PUT</span></td>
                                <td><code>/api/chats/{id}</code></td>
                                <td>Update chat</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>name, description</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-danger">DELETE</span></td>
                                <td><code>/api/chats/{id}</code></td>
                                <td>Delete chat</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>None</td>
                            </tr>

                            <!-- Message Endpoints -->
                            <tr class="table-info">
                                <td colspan="5"><strong>Messages</strong></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">GET</span></td>
                                <td><code>/api/chats/{id}/messages</code></td>
                                <td>Get chat messages</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>page, per_page, before, after</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">POST</span></td>
                                <td><code>/api/chats/{id}/messages</code></td>
                                <td>Send message</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>type, content, media_url, reply_to_id</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">PUT</span></td>
                                <td><code>/api/messages/{id}</code></td>
                                <td>Update message</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>content</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-danger">DELETE</span></td>
                                <td><code>/api/messages/{id}</code></td>
                                <td>Delete message</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>None</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">POST</span></td>
                                <td><code>/api/messages/{id}/react</code></td>
                                <td>React to message</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>reaction</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">POST</span></td>
                                <td><code>/api/messages/{id}/read</code></td>
                                <td>Mark message as read</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>None</td>
                            </tr>

                            <!-- Contact Endpoints -->
                            <tr class="table-info">
                                <td colspan="5"><strong>Contacts</strong></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">GET</span></td>
                                <td><code>/api/contacts</code></td>
                                <td>List contacts</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>search, blocked, favorites</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">POST</span></td>
                                <td><code>/api/contacts</code></td>
                                <td>Add contact</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>phone_number, name</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">PATCH</span></td>
                                <td><code>/api/contacts/{id}/block</code></td>
                                <td>Block/unblock contact</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>is_blocked</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">PATCH</span></td>
                                <td><code>/api/contacts/{id}/favorite</code></td>
                                <td>Add/remove from favorites</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>is_favorite</td>
                            </tr>

                            <!-- Status Endpoints -->
                            <tr class="table-info">
                                <td colspan="5"><strong>Status Updates</strong></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">GET</span></td>
                                <td><code>/api/status</code></td>
                                <td>Get status updates</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>None</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">POST</span></td>
                                <td><code>/api/status</code></td>
                                <td>Upload status</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>type, content, media_url, background_color, privacy</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">POST</span></td>
                                <td><code>/api/status/{id}/view</code></td>
                                <td>Mark status as viewed</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>None</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-danger">DELETE</span></td>
                                <td><code>/api/status/{id}</code></td>
                                <td>Delete status</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>None</td>
                            </tr>

                            <!-- Call Endpoints -->
                            <tr class="table-info">
                                <td colspan="5"><strong>Calls</strong></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">GET</span></td>
                                <td><code>/api/calls</code></td>
                                <td>Get call history</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>type, status, page</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">POST</span></td>
                                <td><code>/api/calls</code></td>
                                <td>Initiate call</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>chat_id, call_type</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">PATCH</span></td>
                                <td><code>/api/calls/{id}/answer</code></td>
                                <td>Answer call</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>None</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-danger">PATCH</span></td>
                                <td><code>/api/calls/{id}/end</code></td>
                                <td>End call</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>None</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">PATCH</span></td>
                                <td><code>/api/calls/{id}/decline</code></td>
                                <td>Decline call</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>None</td>
                            </tr>

                            <!-- Media Endpoints -->
                            <tr class="table-info">
                                <td colspan="5"><strong>Media</strong></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">POST</span></td>
                                <td><code>/api/media/upload</code></td>
                                <td>Upload media file</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>file, type</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">GET</span></td>
                                <td><code>/api/media/{id}</code></td>
                                <td>Get media file</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>None</td>
                            </tr>

                            <!-- Settings Endpoints -->
                            <tr class="table-info">
                                <td colspan="5"><strong>Settings</strong></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">GET</span></td>
                                <td><code>/api/settings</code></td>
                                <td>Get user settings</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>None</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning">PUT</span></td>
                                <td><code>/api/settings</code></td>
                                <td>Update settings</td>
                                <td><i class="bi bi-check text-success"></i></td>
                                <td>Various setting fields</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 