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
        <div class="for_ajax_load"></div>
        <div class="clearfix"> </div>
        <!-- END HEADER & CONTENT DIVIDER -->
        <!-- BEGIN CONTAINER -->
            <div class="page-container">
                <!-- BEGIN SIDEBAR -->
                @include('layouts.menu')
                <div class="page-content-wrapper">
                    <!-- BEGIN CONTENT BODY -->
                    <div class="page-content">
                        <div style="max-width:1200px; margin:auto;">
                            <p class="page_header">Industry Analysis</p>
                            <div class="analysisSimpleAlert">
                                <p>
                                    Industry Research is a tool to give you insight into different topics and industries on YouTube. Fill out 5-15 keywords, and you will receive various data that showcases how the average video and channel looks, in result for that specific topic.
                                </p>
                                <span class="analysisAlertClose">X</span>
                            </div>
                            <div class="row">
                                <div class="dashboard-stat2 ">

                                    @if(!isset($data['views']))
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            {{Form::open(array('action' => 'AnalysisController@getVideos'))}}
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
                                                            <select class="form-control" id="country-select" name="country">
                                                                <option value="us" checked>USA</option>
                                                                <option value="de">Germany</option>
                                                                <option value="gb">UK</option>
                                                                <option value="jp">Japan</option>
                                                                <option value="in">India</option>
                                                                <option value="dk">Denmark</option>
                                                                <option value="ca">Canada</option>
                                                                <option value="fr">France</option>
                                                                <option value="kr">South Korea</option>
                                                                <option value="ru">Russia</option>
                                                                <option value="br">Brazil</option>
                                                                <option value="mx">Mexico</option>
                                                            </select>
                                                            {{--{{ Form::select('country',$countryKeyword,null,array('class'=>'form-control','id'=>'lang-select')) }}--}}
                                                            <input style="width: 190px;" type="hidden" name="location" id="location" placeholder="36.859579,-76.187269 e.g.">
                                                            <input type="hidden" name="max_res_0" value="100" style="width:50px;margin-top: 24px;" placeholder="100"/>
                                                        </div>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <div class="form-group">
                                                    <label for="comment">Keywords</label>
                                                    <textarea class="form-control term1" name="term_1" rows="15" placeholder="Enter 5-15 keywords - one per line" id="keywords" required></textarea>
                                                </div>
                                                <div class="modal-footer controls" style="text-align:left;">
                                                    <button type="submit" name="submit" class="btn green start" id="analysis_search">Submit</button>
                                                </div>
                                            </div>
                                            {{ Form::close() }}
                                        </div>
                                    </div>
                                    @else
                                        <div class="row">
                                            <span class="section_title col-lg-9 col-sm-12 col-xs-12" style="margin-top:4%;color:#FF7474;font-size:24px">Average Video</span>
                                            <div class="col-lg-3 col-sm-12 col-xs-12 dow_csv_cont">
                                                <a href="analysis/csv" class="btn btn-xs btn-success pull-right download_csv_buttom">Download CSV</a>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-4 averageStat">
                                                <span class="section_title averageStatTitle">Views</span>
                                                <span class="averageStatResult"><?php echo $data['views'] ?></span>
                                            </div>
                                            <div class="col-lg-4 averageStat">
                                                <span class="section_title averageStatTitle">Video Length</span>
                                                <span class="averageStatResult"><?php echo $data['videoLength'] ?><br>minutes</span>
                                            </div>
                                            <div class="col-lg-4 averageStat">
                                                <span class="section_title averageStatTitle">With Subtitles</span>
                                                <span class="averageStatResult"><?php echo $data['subtitle'] ?>%</span>
                                            </div>

                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6 averageStat">
                                                <span class="section_title averageStatTitle">Video Age</span>
                                                <span class="averageStatResult"><?php echo $data['videoAge'] ?><br>days</span>
                                            </div>
                                            <div class="col-lg-6 averageStat">
                                                <span class="section_title averageStatTitle">Uploaded last 30 days</span>
                                                <span class="averageStatResult"><?php echo $data['uploadedLast30Days'] ?></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <span class="section_title col-lg-9 col-sm-12 col-xs-12" style="margin-top:4%;color:#FF7474;font-size:24px">Average Engagement</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-4 averageStat">
                                                <span class="section_title averageStatTitle">Likes</span>
                                                <span class="averageStatResult"><?php echo $data['like'] ?></span>
                                            </div>
                                            <div class="col-lg-4 averageStat">
                                                <span class="section_title averageStatTitle">Dislikes</span>
                                                <span class="averageStatResult"><?php echo $data['disLike'] ?></span>
                                            </div>
                                            <div class="col-lg-4 averageStat">
                                                <span class="section_title averageStatTitle">Comments</span>
                                                <span class="averageStatResult"><?php echo $data['comment'] ?></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-6 averageStat">
                                                <span class="section_title averageStatTitle">Total Engagement</span>
                                                <span class="averageStatTitle" style="margin-top:-20px;font-size:12px">(Likes+Dislikes+Comments)</span>
                                                <span class="averageStatResult"><?php echo $data['totalEngagement'] ?></span>
                                            </div>
                                            <div class="col-lg-6 averageStat">
                                                <span class="section_title averageStatTitle">Total Engagement Per View</span>
                                                <span class="averageStatResult"><?php echo $data['totalEngagementPerView'] ?></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <span class="section_title col-lg-9 col-sm-12 col-xs-12" style="margin-top:4%;color:#FF7474;font-size:24px">Average Channel</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-2"></div>
                                            <div class="col-sm-8" >
                                                <div class="row">
                                                    <div class="col-lg-4 averageStat">
                                                        <span class="section_title averageStatTitle">Channel Subscribers</span>
                                                        <span class="averageStatResult"><?php echo $data['channelSubscriber'] ?></span>
                                                    </div>
                                                    <div class="col-lg-4 averageStat">
                                                        <span class="section_title averageStatTitle">Channel Age</span>
                                                        <span class="averageStatResult"><?php echo $data['channelAge'] ?></span>
                                                    </div>
                                                    <div class="col-lg-4 averageStat">
                                                        <span class="section_title averageStatTitle">Channel Videos</span>
                                                        <span class="averageStatResult"><?php echo $data['channelVideo'] ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-2"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @include('layouts.footer')

        <div class="loading">
            <div class="spinner">
                <div class="bar1"></div>
                <div class="bar2"></div>
                <div class="bar3"></div>
                <div class="bar4"></div>
                <div class="bar5"></div>
                <div class="bar6"></div>
                <div class="bar7"></div>
                <div class="bar8"></div>
                <div class="bar9"></div>
                <div class="bar10"></div>
                <div class="bar11"></div>
                <div class="bar12"></div>
            </div>
        </div>

    </body>

</html>