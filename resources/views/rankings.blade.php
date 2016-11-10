<!DOCTYPE html>
<html lang="en">
    <head>
        @include('layouts.header')        
    </head>
    <!-- END HEAD -->

    <body class="page-header-fixed page-sidebar-closed-hide-logo page-container-bg-solid">
        @include('layouts.pageheader')
        <!-- END HEADER -->
        <!-- BEGIN HEADER & CONTENT DIVIDER -->
        <div class="clearfix"> </div>
        <!-- END HEADER & CONTENT DIVIDER -->
        <!-- BEGIN CONTAINER -->
        <div class="page-container">
            <!-- BEGIN SIDEBAR -->
            <div class="page-sidebar-wrapper">                                
                <div class="page-sidebar navbar-collapse collapse">                    
                    <ul class="page-sidebar-menu  page-header-fixed page-sidebar-menu-hover-submenu " data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200">
                        <li class="nav-item start">
                            <a href="dashboard" class="nav-link nav-toggle">
                                <i class="icon-home"></i>
                                <span class="title">Dashboard</span>
                                <span class="selected"></span>
                                <span class="arrow"></span>
                            </a>                            
                        </li>
                        <li class="nav-item  active open">
                            <a href="rankings" class="nav-link nav-toggle">
                                <i class="icon-bar-chart"></i>
                                <span class="title">Rankings</span>
                                <span class="selected"></span>
                                <span class="arrow open"></span>
                            </a>                            
                        </li>
                    </ul>                    
                </div>                
            </div>
            <div class="page-content-wrapper">
                <!-- BEGIN CONTENT BODY -->                
                <div class="page-content">
                    <p class="page_header">Keyword Rankings</p>
                    <div class="row">
                        <div class="dashboard-stat2 ">
                            <p class="section_title">Average Keyword Rankings</p>
                            <div id="container" style="height: 350px;"></div> 
                        </div>                        
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="dashboard-stat2" style="overflow: auto;">  
                                <div class="col-lg-3">
                                    <p class="section_title">Keyword Rankings</p>
                                    <a class="btn green addkeyword" data-toggle="modal" data-target="#addKeyword">
                                        Add New
                                        <i class="fa fa-plus"></i>
                                    </a>         
                                    
                                    <!-- Modal -->
                                    
                                        
                                    <div id="addKeyword" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                        <!-- Modal content-->
                                        <div class="modal-content"> 
                                            
                                                {{Form::open(array('action' => 'DashboardController@startSearch'))}}
                                                <div class="modal-header">
                                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                    <h4 class="modal-title add_key_modal">Add New Keywords</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="bar-wrapper" style="display:none;">
                                                        <div class="bar" id="bar">
                                                            <div class="progress" id="progress"></div>
                                                        </div>
                                                        <span class="pg-text" id="pg-text">0%</span>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-lg-4">                                                            
                                                                <label for="country">Country:</label>
                                                                {{ Form::select('country',$countryKeyword,null,array('class'=>'form-control','id'=>'lang-select')) }}
                                                                <input style="width: 190px;" type="hidden" name="location" id="location" placeholder="36.859579,-76.187269 e.g.">
                                                                <input type="hidden" name="max_res_0" value="100" style="width:50px;margin-top: 24px;" placeholder="100"/>
                                                            </div>                                                        
                                                        </div>
                                                    </div>

                                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                        <div class="form-group">
                                                            <label for="comment">Keyword(s)</label>
                                                            <textarea class="form-control term1" name="term_1" rows="5" id="comment"></textarea>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="row">
                                                                <div class="col-lg-8">
                                                                    <label for="usr">Preferred Video (Optional)</label>
                                                                    <input type="text" class="form-control" id="video_url" name="video_url" placeholder="Enter Video URL">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="row">
                                                                <div class="col-lg-6">
                                                                    <label for="group">Add To Keyword Group (Optional)</label>
                                                                    {{Form::select(null,$group,null,array('class'=>'form-control','id'=>'group'))}}
                                                                </div>
                                                            </div>
                                                        </div>                                                   

                                                    <a href="" id="link" style="cursor:default; opacity:0;" target="_blank">dl</a>
                                                </div>
                                                <div class="modal-footer controls" style="text-align:left;">
                                                    <button type="submit" name="submit" class="btn green start" id="start_search">Submit</button>
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                                </div>   
                                            {{ Form::close() }}
                                        </div>
                                      </div>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    @if(isset($topData))
                                    @foreach($topData['tops'] as $top)
                                    <div class="stats_square">
                                        <span class="color_top
                                                @if(isset($topData['yesterday'][$top]) && $topData['yesterday'][$top]>0)
                                                top_up @endif">Top {{$top}}</span>
                                        <span class="num_group">{{$topData['today'][$top]}}/{{$topData['total']}}</span>
                                        @if(isset($topData['yesterday'][$top]))
                                            <span class="up_or_down">
                                                @if($topData['yesterday'][$top]>0)
                                                <span class="up_or_down"><span class="rank_color up">▲</span>{{abs($topData['yesterday'][$top])}}</span>
                                                @elseif($topData['yesterday'][$top]<0)
                                                <span class="up_or_down"><span class="rank_color down">▼</span>{{abs($topData['yesterday'][$top])}}</span>
                                                @else
                                                <span class="up_or_down">{{abs($topData['yesterday'][$top])}}</span>
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                    @endforeach
                                    @endif
                                </div>
                            
                                <div class="row">
                                    <div class="col-md-12">
                                         <!-- Modal Start here-->
                                        <div class="modal fade bs-example-modal-sm" id="myPleaseWait" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" style='z-index:99999;'>
                                            <div class="modal-dialog modal-sm">
                                                <p class='please_wait'>Please Wait...</p>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="confirm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title" id="myModalLabel">Deleting</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        Are you sure you want to delete X keywords
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default confirmClose" data-dismiss="modal">No</button>
                                                        <button type="button" class="btn btn-danger confirmDelete">Yes</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="addKeywordGroup" tabindex="-1" role="dialog" aria-labelledby="myModaladdKeywordGroupLabel">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title" id="myModaladdKeywordGroupLabel">Add to Keyword Group</h4>
                                                    </div>
                                                    <div class="modal-body col-md-12">

                                                        <div class="form-group col-md-6">
                                                            <label for="groups"><b>Choose Group</b></label>
                                                            {{Form::select(null,$group,null,array('class'=>'form-control','id'=>'groupsPopUp'))}}
                                                        </div>
                                                        <div class="createGroup col-md-6 hide">
                                                            <label for="groups"><b>Create Group</b></label>
                                                            {{Form::text('createGroup',null,array('class'=>'form-control','placeholder'=>'please fill new group name'))}}
                                                        </div>
                                                        <div class="keywordGrouplist col-md-6">

                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default confirmClose" data-dismiss="modal">close</button>
                                                        <button type="button" class="btn btn-success createKeyword">Create group</button>
                                                        <button type="button" class="btn btn-info chooseKeywordGroup">Save </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="showGraphModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title" id="showGraphModalModalLabel">Show Graph</h4>
                                                    </div>
                                                    <div class="modal-body" id="showGraphModalBody">

                                                    </div>
                                                    <div class="modal-footer">

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="removeKeywordFromGroupModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title" id="removeKeywordFromGroupModalModalLabel">Remove Keyword From Group</h4>
                                                    </div>
                                                    <div class="modal-body" id="removeKeywordFromGroupModalModalBody">

                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-default removeKeywordGroup" data-dismiss="modal">No</button>
                                                        <button type="button" class="btn btn-danger removeKeywordGroupConfirm">Yes</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

            <!-- Modal ends Here -->

                                        <!-- BEGIN EXAMPLE TABLE PORTLET-->
                                        <div class="portlet light ">

                                            <div class="portlet-body">
                                                <table class="table table-striped table-bordered table-hover table-checkable order-column" id="sample_1">
                                                    <div class="col-lg-3 col-sm-12 col-xs-12 selectboxSpace">
                                                        <div class="form-group">
                                                            <label for="actions">Action</label>
                                                            <select class="form-control" id="actions">
                                                                <option>Select action</option>
                                                                <option name="showGraph">Show Graph</option>
                                                                <option name="showIndividualGraph">Show Individual Graph</option>
                                                                <option name="delete">Delete Keywords</option>
                                                                <option name="AddtoKeywordGroup">Add to Keyword Group</option>
                                                                <option name="removeFromKeywordGroup">Remove from Keyword Group</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-sm-12 col-xs-12 selectboxSpace">
                                                        <div class="form-group">
                                                            <label for="groups">Keyword Group</label>
                                                            {{Form::select(null,$group,null,array('class'=>'form-control','id'=>'groups'))}}
                                                            {{--<select class="form-control" id="groups">--}}
                                                                {{--<option value="">All</option>--}}
                                                                {{--<option value="First">First Group</option>--}}
                                                                {{--<option value="Second">Second Group</option>--}}
                                                                {{--<option value="Third">Third Group</option>--}}
                                                            {{--</select>--}}
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-sm-12 col-xs-12 selectboxSpace">
                                                        <div class="form-group">
                                                            <label for="select_country">Country</label>
                                                            {{ Form::select(null,$country,null,array('class'=>'form-control','id'=>'select_country')) }}
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 col-sm-12 col-xs-12 selectboxSpace dow_csv_cont">
                                                        <div class="form-group">
                                                            <a href="rankings/csv" class="btn btn-xs btn-success pull-right download_csv_buttom">Download CSV</a>
                                                        </div>

                                                    </div>

                                                    <thead>
                                                        <tr>
                                                            <th>
                                                                <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                                                    <input type="checkbox" class="group-checkable" data-set="#sample_1 .checkboxes" />
                                                                    <span></span>
                                                                </label>
                                                            </th>
                                                            <th> Video </th>
                                                            <th> Keyword </th>
                                                            <th class="rankSort"> Rank </th>
                                                            <th> Day </th>
                                                            <th> Week </th>
                                                            <th> Month </th>
                                                            <th> Country </th>
                                                            <th> URL </th>
                                                            <th> Actions </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @if(!empty($alldata))
                                                        @foreach($alldata as $data)
                                                        <tr class="odd gradeX" data-keywords="{{$data['keyword']}}" data-keyid="{{$data['keyword_id']}}">
                                                            <td>
                                                                <label class="mt-checkbox mt-checkbox-single mt-checkbox-outline">
                                                                    <input type="checkbox" class="checkboxes" value="1" />
                                                                    <span></span>
                                                                </label>
                                                            </td>
                                                            <td class="video_name"  data-toggle="modal" data-target="#perfered_video">
                                                                @if($data['preferred'])
                                                                    <span class="preferred">P</span>
                                                                @endif
                                                                <span class="video_title
                                                                    @if($data['preferred'] || $data['title'] == '(nothing found in top results)')
                                                                        video_title_font
                                                                    @endif
                                                                ">{{$data['title']}}</span>
                                                                <div class="count">
                                                                    @if($data['count'] != 1)
                                                                    ({{$data['count']}})
                                                                    <div class="detail">
                                                                        @if(isset($data['others']))
                                                                        @foreach($data['others'] as $other)
                                                                            <div class="other">
                                                                                @if($other['preferred'])
                                                                                <span class="preferred">P</span>
                                                                                @endif
                                                                                <span class="other_name">
                                                                                {{$other['video_name'].' #'.$other['rating']}}
                                                                                </span>
                                                                            </div>
                                                                        @endforeach
                                                                        @endif
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                            <td>{{$data['keyword']}}</td>
                                                            <td>{{$data['rank']}}</td>
                                                            <td>
                                                                @if(isset($prevData[$data['keyword']]['day']))
                                                                    @if($prevData[$data['keyword']]['day']['rating']>0)
                                                                    <span class="rank_color down">▼ </span>
                                                                    @elseif($prevData[$data['keyword']]['day']['rating']<0)
                                                                    <span class="rank_color up">▲ </span>
                                                                    @endif
                                                                    {{abs($prevData[$data['keyword']]['day']['rating'])}}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if(isset($prevData[$data['keyword']]['week']))
                                                                    @if($prevData[$data['keyword']]['week']['rating']<0)
                                                                        <span class="rank_color down">▼ </span>
                                                                    @elseif($prevData[$data['keyword']]['week']['rating']>0)
                                                                        <span class="rank_color up">▲ </span>
                                                                    @endif
                                                                    {{abs($prevData[$data['keyword']]['week']['rating'])}}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if(isset($prevData[$data['keyword']]['month']))
                                                                    @if($prevData[$data['keyword']]['month']['rating']<0)
                                                                        <span class="rank_color down">▼ </span>
                                                                    @elseif($prevData[$data['keyword']]['month']['rating']>0)
                                                                        <span class="rank_color up">▲ </span>
                                                                    @endif
                                                                    {{abs($prevData[$data['keyword']]['month']['rating'])}}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td data-country="{{$data['country']}}"> {{$data['country']}} </td>
                                                            <td><a href="https://www.youtube.com/watch?v={{$data['url']}}" target="_blank">{{$data['url']}}</a></td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <button class="btn btn-xs green dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false"> Actions
                                                                        <i class="fa fa-angle-down"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown_menu_pos" role="menu">
                                                                        <li>
                                                                            <a data-keyword="{{$data['keyword']}}" class="action-link-graph" href="javascript:void(0)">
                                                                                <i class="icon-docs"></i> Show Graph
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <a data-keyword="{{$data['keyword']}}" class="action-link-delete" href="javascript:void(0)">
                                                                                <i class="icon-tag"></i>Delete Keyword
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <!-- END EXAMPLE TABLE PORTLET-->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="perfered_video" class="modal fade" role="dialog">
                    <div class="modal-dialog">

                    <!-- Modal content-->
                        {{Form::open(array('action' => 'DashboardController@setPreferred'))}}
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 class="modal-title add_key_modal">Track Perferred Video</h4>
                            </div>
                            <div class="modal-body">                                                                
                                <div class="form-group">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <label for="usr">Preferred Video</label>
                                            <input type="text" class="form-control" id="video_url" name="video_url" placeholder="Enter Video URL">
                                            <input type="hidden" name="keyword" id="preferred_keyword" value="">
                                            <input type="hidden" name="keyword_id" id="preferred_keyword_id" value="">
                                            <input type="hidden" name="country" id="preferred_country" value="">
                                            <input type="hidden" name="group" id="preferred_group" value="">
                                            <input type="hidden" name="removePerfered" id="remove_perfered" value="0">
                                        </div>
                                    </div>
                                </div>                                
                            </div>
                          <div class="modal-footer" style="text-align:left;">
                            <button type="submit" class="btn green">Submit</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type='submit' class='btn btn-danger removePerfered hide'>Remove perferred</button>
                          </div>
                        </div>
                        {{Form::close()}}
                    </div>
                </div>
                <!-- END CONTENT BODY -->
            </div>            
        </div>
        <!-- END CONTAINER -->
        <!-- BEGIN FOOTER -->
        <div class="page-footer">
            {{--<div class="page-footer-inner"> 2016 &copy; Metronic Theme By
                <a target="_blank" href="http://keenthemes.com">Keenthemes</a> &nbsp;|&nbsp;
                <a href="http://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes" title="Purchase Metronic just for 27$ and get lifetime updates for free" target="_blank">Purchase Metronic!</a>
                <div class="scroll-to-top">
                    <i class="icon-arrow-up"></i>
                </div>
            </div>--}}
            <!-- END FOOTER -->
            <!-- BEGIN QUICK NAV -->
            <nav class="quick-nav" style="display:none;">
                <a class="quick-nav-trigger" href="#0">
                    <span aria-hidden="true"></span>
                </a>
                <ul>
                    <li>
                        <a href="https://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes" target="_blank" class="active">
                            <span>Purchase Metronic</span>
                            <i class="icon-basket"></i>
                        </a>
                    </li>
                    <li>
                        <a href="https://themeforest.net/item/metronic-responsive-admin-dashboard-template/reviews/4021469?ref=keenthemes" target="_blank">
                            <span>Customer Reviews</span>
                            <i class="icon-users"></i>
                        </a>
                    </li>
                    <li>
                        <a href="http://keenthemes.com/showcast/" target="_blank">
                            <span>Showcase</span>
                            <i class="icon-user"></i>
                        </a>
                    </li>
                    <li>
                        <a href="http://keenthemes.com/metronic-theme/changelog/" target="_blank">
                            <span>Changelog</span>
                            <i class="icon-graph"></i>
                        </a>
                    </li>
                </ul>
                <span aria-hidden="true" class="quick-nav-bg"></span>
            </nav>
            <div class="quick-nav-overlay"></div>
           </div>
        @include('layouts.footer')
    </body>

</html>