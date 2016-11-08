<!-- BEGIN HEADER -->
<?php 
    if(session()->has('profile_data')){
        $profile = session()->get('profile_data');
    }
    if(session()->has('default_channel')){
        $default_channel = session()->get('default_channel');
    }
    if(session()->has('channels')){
        $channels = session()->get('channels');
    }

?>
        <div class="page-header navbar navbar-fixed-top">            
            <!-- BEGIN HEADER INNER -->
            <div class="flash-message">
                @if(Session::has('error'))
                    <p class="alert alert-danger" style="color: red">{{ Session::get('error')}} <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a></p>
                @endif
            </div>
            <div class="page-header-inner ">
                <!-- BEGIN LOGO -->
                <div class="page-logo">
                    <a href="/dashboard">
                        <img src="../assets/layouts/layout2/img/logo-default.png" alt="logo" class="logo-default" /> </a>
                    <div class="menu-toggler sidebar-toggler">
                        <!-- DOC: Remove the above "hide" to enable the sidebar toggler button on header -->
                    </div>
                </div>
                <!-- END LOGO -->
                <!-- BEGIN RESPONSIVE MENU TOGGLER -->
                <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"> </a>
                <!-- END RESPONSIVE MENU TOGGLER -->                
                <!-- BEGIN PAGE TOP -->
                <div class="page-top">
                    <!-- BEGIN TOP NAVIGATION MENU -->
                    <div class="top-menu">
                        <ul class="nav navbar-nav pull-right">
                            <!-- BEGIN USER LOGIN DROPDOWN -->
                            <!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
                            <li class="dropdown dropdown-user">
                                <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                                    @if(Session::has('default_channel'))
                                        <img alt="" class="img-circle" src="{{Session::get('prof_pic')}}" />
                                    @endif
                                    <span class="username username-hide-on-mobile">
                                        @if(Session::has('default_channel'))
                                            {{Session::get('default_channel')->channelname}}
                                            @if(isset($countKeyword))
                                                {{ $countKeyword }}/200
                                            @endif
                                        @else
                                            Add a channel
                                        @endif
                                    </span>
                                    <i class="fa fa-angle-down"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-default">
                                    <li>
                                        <a data-toggle="modal" data-target="#profile_settings">
                                            <i class="icon-user"></i> My Profile </a>
                                    </li>
                                    <li>
                                        <a data-toggle="modal" data-target="#change_channel">
                                            <i class="fa fa-youtube-square"></i>Change Channel</a>
                                    </li>   
                                    <li>
                                        <a data-toggle="modal" data-target="#manage_channels">
                                            <i class="fa fa-cogs"></i>Manage Channels</a>
                                    </li> 
                                    <li class="divider"> </li>                                    
                                    <li>
                                        <a href="logout">
                                            <i class="icon-key"></i> Log Out </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <!-- END TOP NAVIGATION MENU -->
                </div>
                <!-- END PAGE TOP -->
            </div>
            <!-- END HEADER INNER -->
        </div>
<!-- Profile settings Modal -->
        <div class="modal fade" id="profile_settings" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                {!! Form::open(['url' => 'editProfile']) !!}
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">My Profile Settings</h4>
                    </div>
                    <div class="modal-body">
                        
                        <div class="form-group">
                            <label for="firstname">First Name</label>
                            <input type="text" class="form-control profile_inputs" value="{{$profile->firstname}}" id="firstname" name="firstname">
                        </div>
                        <div class="form-group">
                            <label for="lastname">Last Name</label>
                            <input type="text" class="form-control profile_inputs" value="{{$profile->lastname}}" id="lastname" name="lastname">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control profile_inputs" value="{{$profile->email}}" id="email" name="email">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control profile_inputs" id="password" name="password" placeholder="Leave blank if you don't want change password">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm password</label>
                            <input type="password" class="form-control profile_inputs" id="confirm_password" name="confirm_password" placeholder="Leave blank if you don't want change password">
                        </div> 
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" name="submit" id="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
<!-- Manage channel Modal -->
        <div class="modal fade" id="manage_channels" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">                
                <div class="modal-content">
                    {!! Form::open(['url' => 'addChannel']) !!}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Add New Channel</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="channelid">Channel ID</label>
                            <input type="text" class="form-control profile_inputs" value="" id="channelid" name="channelid">
                        </div>
                    </div>
                    <div class="modal-footer" style="border-bottom:1px solid #e5e5e5;">                        
                        <button type="submit" name="submit" id="submit" class="btn btn-primary addchannel">Add Channel</button>
                    </div>
                    {!! Form::close() !!}
                    <div class="modal-header">                        
                        <h4 class="modal-title" id="myModalLabel">My Channels</h4>
                    </div>
                    <div class="modal-body" style="overflow: auto;"> 
                        @foreach($channels as $channel)                        
                        <div class="col-lg-10">
                            <span class="channel_name"><a href="https://www.youtube.com/channel/{{$channel->channelid}}" target="_blank">{{$channel->channelname}}</a></span>
                        </div>
                        <div class="col-lg-2" style="margin-top:3px;">
                            <input type="button" class="btn btn-danger delete" name="{{$channel->id}}" value="Delete">
                        </div>
                        @endforeach
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" name="submit" id="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
                
            </div>
        </div>
<!-- Change channel Modal -->
        <div class="modal fade" id="change_channel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">                
                <div class="modal-content">                    
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Change Channel</h4>
                    </div>
                    <div class="modal-body">                        
                        <div class="form-group">
                            <label for="choose_channel">Choose Channel</label>
                            <select class="form-control profile_inputs" id="choose_channel">
                                @foreach($channels as $channel)
                                    <option name="channel_id" {{($channel->id == Session::get('default_channel')->id)? 'selected':''}} value="{{$channel->id}}">{{$channel->channelname}}</option>
                                @endforeach
                            </select>
                        </div>                                               
                    </div>
                    <div class="modal-footer" style="border-bottom:1px solid #e5e5e5;">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" name="submit" id="submit" class="btn btn-primary change">Change Channel</button>
                    </div>
                </div>                
            </div>
        </div>
@yield('pageheader')