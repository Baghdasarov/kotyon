$(document).ready(function(){
    $(".nav-item a").each(function(index,value) {
        if (value.href == window.location.href) {
            $(this).parent().addClass("start active open");
            $(this).find('.arrow').addClass('open');
            $(this).append("<span class='selected'></span>");
        } else {
            $(this).parent().removeClass("start active open");
            $(this).find('.arrow').removeClass('open');
            $(this).find('.selected').remove();
        }
    })
    $('#start_search').click(function(){
        if($('#lang-select').val() == 'none'){
            alert('choose country');
            return false;
        }
        $('#myPleaseWait').modal('show');
        $('#addKeyword').css('display', 'none');
    });

    $("form #submit").click(function (e) {
        if($('#channelid').val() == ''){
            e.preventDefault();
            $('#channelid').attr('placeholder','*please fill that field');
            $('#channelid').addClass('wrong_placeholder');

        }
    });

    $('.change').click(function(){
        var channel_id = $('#choose_channel').val();
        $.ajaxSetup({
            headers:
            {
                'X-CSRF-Token': $('input[name="_token"]').val()
            }
        });
        $.ajax({
            method: "POST",
            url: "changeChannel",
            data: {channel_id:channel_id},
            success: function(res){
                // console.log(res);
                if(res == 'done'){
                    // console.log(res);
                    window.location.href = 'rankings';
                }
            }
        });
    })
    $('.delete').click(function(){
        $('#myPleaseWait').modal('show');
        $('#manage_channels').css('display', 'none');
        var channel_id = $(this).attr('name');
        $.ajaxSetup({
            headers:
            {
                'X-CSRF-Token': $('input[name="_token"]').val()
            }
        });
        $.ajax({
            method: "POST",
            url: "deleteChannel",
            data: {channel_id:channel_id},
            success: function(res){
                if(res == 'done'){
                    location.reload();
                }
            }
        });
    });

    $('.video_name').click(function () {
        var keyword = $(this).next().text();
        var keyword_id = $(this).parent().data('keyid');
        var country = $(this).next().next().next().next().next().next().text();
        var group = $(this).next().next().next().next().next().next().next().text();
        $('#preferred_keyword').val(keyword);
        $('#preferred_keyword_id').val(keyword_id);
        $('#preferred_country').val(country);
        $('#preferred_group').val(group);

        if($(this).find(':first-child').hasClass('preferred')){
            $('.removePerfered').removeClass('hide');
            $("#perfered_video input[name='removePerfered']").val('1');
        }else{
            $("#perfered_video input[name='removePerfered']").val('0');
            $('.removePerfered').addClass('hide');
        }
        
        // $('.removePerfered').click(function (e) {
        //     e.preventDefault();
        //
        // })
    });

    $('.action-link-delete').click(function () {
        var row = $(this).parent().parent().parent().parent().parent();
        var keyword = $(this).data('keyword');
        var keyword_id = $(this).data('keid');
        var wait = $('#myPleaseWait');

        $('#confirm').modal('show');
        $('#confirm .modal-body').text("Are you sure you want to delete"+ ' "'+keyword+'" '+" keywords");
        $(".confirmClose").click(function () {
            $('#actions option:first-child').attr("selected", "selected");
        });

        $(".confirmDelete").click(function () {
            wait.modal('show');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-Token': $('input[name="_token"]').val()
                }
            });
            $.ajax({
                method: "POST",
                url: "deleteKeyword",
                data: {keyword: keyword,keyword_id:keyword_id},
                success: function (res) {
                    if (res == 'success') {
                        row.hide();
                    }
                    wait.modal('hide');
                    $('#confirm').modal('hide');
                }
            });
        });
    });

    $(".group-checkable").change(function () {
        if($(".group-checkable").is(':checked')){
            $(".checkboxes").prop('checked',true);
        }else{
            $(".checkboxes").prop('checked',false);
        }
    })
    $('.action-link-graph').click(function () {
        var keyword = $(this).data('keyword');
        $.ajax({
            url: "rankingsJson",
            data: {keyword:keyword},
            success: function(res){
                $(document).scrollTop(50);
                seriesOptions[0] = {
                    name: keyword,
                    data: res
                };
                createChart();
            }
        });
    });
    var seriesOptions = [],
        seriesCounter = 0,
        // names = ['GOOG'];
        names = ['Average'];
//        names = ['MSFT', 'AAPL', 'GOOG'];

    /**
     * Create the chart when all data is loaded
     * @returns {undefined}
     */
    function createChart() {

        $('#container').highcharts('StockChart', {

            rangeSelector: {
                selected: 4
            },
            yAxis: {
                reversed: true,
                labels: {
                    formatter: function () {
                        return (this.value > 0 ? ' + ' : '') + this.value; //+ '%';
                    }
                },
                plotLines: [{
                    value: 0,
                    width: 2,
                    color: 'silver'
                }]
            },

            plotOptions: {
                series: {
                    compare: 'value'
                }
            },
            tooltip: {
                pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.change})<br/>',
                valueDecimals: 2
            },

            series: seriesOptions
        });
    }
    if($('div').hasClass('for_ajax_load_rankings')){
        $.each(names, function (i, name) {
            // var sample = 'https://www.highcharts.com/samples/data/jsonp.php?filename=' + name.toLowerCase() + '-c.json&callback=?';
            $.getJSON('/rankingsJson', function (data) {
                seriesOptions[i] = {
                    name: name,
                    data: data
                };
                // console.log(seriesOptions);
                // As we're loading the data asynchronously, we don't know what order it will arrive. So
                // we keep a counter and create the chart when all the data is loaded.
                seriesCounter += 1;

                if (seriesCounter === names.length) {
                    createChart();
                }
            });
        });
    }
    var table = $('#sample_1').DataTable({
        "aLengthMenu": [[25, 50, 100], [25, 50, 100]],
        "iDisplayLength": 25
    });

    // $(".rankSort").click();

    $('#select_country').on('change',(function () {
        table.column(7).search(this.value).draw();
    })
    );

    var keywords = [];
    var keywordsId = [];
    var countKeyword = 0;
    $('#groups').change(function () {
        if(this.value == 0){
            table.column(8).search('').draw()
        }else{
            table.column(8).search(this.value).draw();
        }
        $.ajax({
            url: "rankingsJson",
            data: {groupAll:$(this).val()},
            success: function(res){
                seriesOptions[0] = {
                    data: res
                };
                // console.log(seriesOptionsPopUp);
                $('#container').highcharts('StockChart', {

                    rangeSelector: {
                        selected: 4
                    },
                    yAxis: {
                        reversed: true,
                        labels: {
                            formatter: function () {
                                return (this.value > 0 ? ' + ' : '') + this.value;
                            }
                        },
                        plotLines: [{
                            value: 0,
                            width: 2,
                            color: 'silver'
                        }]
                    },

                    plotOptions: {
                        series: {
                            compare: 'value'
                        }
                    },

                    tooltip: {
                        pointFormat: '<span style="color:{series.color}">Average</span>: <b>{point.y}</b> ({point.change})<br/>',
                        valueDecimals: 2
                    },

                    series: seriesOptions
                });
            }
        });
        removeKeywordFromGroup(countKeyword,keywordsId);

    });

    $(document).on('change', '#actions', function(e) {
        $(".alert_bootsrap").remove();
        $('#actions option:first-child').attr("selected", false);
        var checked = '';
        var count = 0;
        keywords = [];
        keywordsId = [];
        var row =  [];
        $(".checkboxes").each(function () {
            if(this.checked == true){
                checked = 'go';
                count ++;
                keywords.push($(this).parents(":eq(2)").data('keywords'));
                keywordsId.push($(this).parents(":eq(2)").data('keyid'));
                row.push($(this).parents(":eq(2)"));
                countKeyword = count;
            }
        });
        if(checked.length==0){
            e.preventDefault();
            $('#actions option:first-child').attr("selected", "selected");
            $(this).after(
                '<div class="alert_bootsrap alert alert-danger alert-dismissable">'+
                    '<button type="button" class="close" ' +
                        'data-dismiss="alert" aria-hidden="true">' +
                        '&times;' +
                    '</button>' +
                    'Please select keywords' +
                '</div>');
        }else{
            if($("#actions option:selected").attr('name') == 'delete'){
                    e.preventDefault();
                    $('#confirm').modal('show');
                    $('#confirm .modal-body').text("Are you sure you want to delete "+count+" keywords");
                    $(".confirmClose").click(function () {
                        $('#actions option:first-child').attr("selected", "selected");
                    });

                    $(".confirmDelete").click(function () {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-Token': $('input[name="_token"]').val()
                            }
                        });
                        $.ajax({
                            method: "POST",
                            url: "deleteKeyword",
                            data: {keyword:keywordsId},
                            success: function(res){
                                if(res == 'success'){
                                    $.each(row,function (item,value) {
                                        value.hide();
                                    });
                                }
                                $('#confirm').modal('hide');
                            }
                        });
                    });

            }else if($("#actions option:selected").attr('name') == 'AddtoKeywordGroup'){
                e.preventDefault();
                $('#addKeywordGroup').modal('show');
                $('.keywordGrouplist').html('<b>List keywords</b>');

                var selectedGroup = $('#groupsPopUp').val();
                $('#groupsPopUp').change(function () {
                    selectedGroup = $('#groupsPopUp').val();
                });

                $.each(keywords,function (item,value) {
                    $('.keywordGrouplist').append('<span style="display: block">'+value+'</span>');
                });

                $('#groupsPopUp').change(function () {
                    selectedGroup = $('#groupsPopUp').val();
                });


                $(".createKeyword").click(function () {
                    if($('.createGroup').hasClass('hide')){
                        $('.createGroup').removeClass('hide');
                        $('#groupsPopUp').parent().addClass('hide');
                        $('.createKeyword').text('Choose group');
                    }else{
                        $('.createGroup').addClass('hide');
                        $('#groupsPopUp').parent().removeClass('hide');
                        $('.createKeyword').text('Create group');
                    }
                });
                var keywordsForSave = keywordsId;
                $('.chooseKeywordGroup').click(function () {
                    if($('.createGroup').hasClass('hide')){
                        selectedGroup = $('#groupsPopUp').val();
                    }else{
                        selectedGroup = $(".createGroup input[name='createGroup']").val();
                    }
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-Token': $('input[name="_token"]').val()
                        }
                    });
                    $.ajax({
                        method: "POST",
                        url: "changeKeywordGroup",
                        data: {keyword:keywordsForSave,group:selectedGroup,removeKeywordFromGroups:0},
                        success: function(res){
                            if(res == 'success'){
                               location.reload();
                            }
                            $('#confirm').modal('hide');
                        }
                    });
                })
            }else if($("#actions option:selected").attr('name') =='showGraph'){
                e.preventDefault();
                $('#showGraphModal').modal('show');
                var seriesOptionsPopUp = [];
                $.ajax({
                    url: "rankingsJson",
                    data: {keyword:keywordsId},
                    success: function(res){
                        seriesOptionsPopUp[0] = {
                            name: keywords,
                            data: res
                        };
                        // console.log(seriesOptionsPopUp);
                        createChartOption();
                    }
                });


                function createChartOption() {
                    $('#showGraphModalBody').highcharts('StockChart', {

                        chart: {
                            renderTo: 'showGraphModalBody',
                            type: 'line',
                            // Explicitly tell the width and height of a chart
                            width: 900,
                            height: 500
                        },
                        navigator: {
                            enabled: false
                        },
                        rangeSelector: {
                            inputEnabled:false,
                            allButtonsEnabled: false,
                            buttons: [{
                                type: 'day',
                                count: 7,
                                text: '1 Week',
                                // dataGrouping: {
                                //     forced: true,
                                //     units: [['day', [1]]]
                                // }
                            }, {
                                type: 'week',
                                count: 5,
                                text: '1 Month',
                                // dataGrouping: {
                                //     forced: true,
                                //     units: [['week', [1]]]
                                // }
                            },{
                                type: 'month',
                                count: 6,
                                text: '6 Month',
                                // dataGrouping: {
                                //     forced: true,
                                //     units: [['month', [1]]]
                                // }
                            }, {
                                type: 'all',
                                text: 'All',
                            }],
                            buttonTheme: {
                                width: 60
                            },
                            selected: 0
                        },


                        yAxis: {
                            reversed: true,
                            labels: {
                                formatter: function () {
                                    return (this.value > 0 ? ' + ' : '') + this.value;
                                }
                            },
                            plotLines: [{
                                value: 0,
                                width: 2,
                                color: 'silver'
                            }]
                        },

                        plotOptions: {
                            series: {
                                compare: 'value'
                            }
                        },

                        tooltip: {
                            pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.change})<br/>',
                            valueDecimals: 2
                        },

                        series: seriesOptionsPopUp
                    });
                }

            }else if($("#actions option:selected").attr('name') =='showIndividualGraph'){
                e.preventDefault();
                if(keywordsId.length<=10){
                    $('#showGraphModal').modal('show');
                    var seriesOptionsPopUp = [];
                    $.ajax({
                        url: "rankingsJsonOption",
                        data: {keyword:keywordsId},
                        success: function(res){
                            $.each(res,function (item,value) {
                                seriesOptionsPopUp[item] = {
                                    name: keywords[item],
                                    data: value
                                };
                            })
                            createChartOptionIndev();
                        }
                    });


                    function createChartOptionIndev() {
                        $('#showGraphModalBody').highcharts('StockChart', {
                            chart: {
                                renderTo: 'showGraphModalBody',
                                type: 'line',
                                width: 900,
                                height: 500
                            },
                            legend: {
                                enabled: true
                            },
                            navigator: {
                                enabled: false
                            },
                            rangeSelector: {
                                inputEnabled:false,
                                allButtonsEnabled: false,
                                buttons: [{
                                    type: 'day',
                                    count: 7,
                                    text: '1 Week',
                                    // dataGrouping: {
                                    //     forced: true,
                                    //     units: [['day', [1]]]
                                    // }
                                }, {
                                    type: 'week',
                                    count: 5,
                                    text: '1 Month',
                                    // dataGrouping: {
                                    //     forced: true,
                                    //     units: [['week', [1]]]
                                    // }
                                },{
                                    type: 'month',
                                    count: 6,
                                    text: '6 Month',
                                    // dataGrouping: {
                                    //     forced: true,
                                    //     units: [['month', [1]]]
                                    // }
                                }, {
                                    type: 'all',
                                    text: 'All',
                                }],
                                buttonTheme: {
                                    width: 60
                                },
                                selected: 0
                            },

                            colors: ['#7cb5ec', 'orange', 'green', 'red', 'purple', 'brown','#15f600','#8500bc','#00eaff','#ef5e00'],

                            yAxis: {
                                reversed: true,
                                labels: {
                                    formatter: function () {
                                        return (this.value > 0 ? ' + ' : '') + this.value ;
                                    }
                                },
                                plotLines: [{
                                    value: 0,
                                    width: 2,
                                    color: 'silver'
                                }]
                            },

                            plotOptions: {
                                series: {
                                    compare: 'value'
                                }
                            },

                            tooltip: {
                                pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.change})<br/>',
                                valueDecimals: 2
                            },
                            series: seriesOptionsPopUp
                        });
                    }
                }else{
                    $('#actions option:first-child').attr("selected", "selected");
                    keywords = [];
                    keywordsId = [];
                    $('#actions').after(
                        '<div class="alert_bootsrap alert alert-danger alert-dismissable">'+
                        '<button type="button" class="close" ' +
                        'data-dismiss="alert" aria-hidden="true">' +
                        '&times;' +
                        '</button>' +
                        'You can select only 10 keywords' +
                        '</div>')
                }

            }else if($("#actions option:selected").attr('name') == 'removeFromKeywordGroup'){
                e.preventDefault();
                removeKeywordFromGroup(countKeyword,keywords);
            }
        }

        $(document).on('click', '.confirmClose', function() {
            $('#actions option:first-child').attr("selected", "selected");
            keywords =  [];
            keywordsId =  [];
        });

        $(document).on('click', '.modal .close', function() {
            $('#actions option:first-child').attr("selected", "selected");
            keywords =  [];
            keywordsId =  [];
        });

        $(document).on('click', '.modal:not(modal-dialog)', function() {
            $('#actions option:first-child').attr("selected", "selected");
            keywords =  [];
            keywordsId =  [];
        });

    });



    $(".loading").hide();
    if($('div').hasClass("for_ajax_load")){
        $(".clickabilitiSimpleAlert").show();
        $(".clickabilityAlertClose").click(function () {
            $(".clickabilitiSimpleAlert").hide();
        });
        $(".loading").show();
        $.ajaxSetup({
            headers: {
                'X-CSRF-Token': $('input[name="_token"]').val()
            }
        });
        $.ajax({
            method: "get",
            url: "clickability",
            success: function(res){
                // console.log(res);
                $.each(res,function (item,value) {
                    var valRes = [];
                    valRes = res[item];

                    seriasPieChartData =
                    [{
                        name: item,
                        colorByPoint: true,
                        data:
                            valRes
                    }]
                    $(".loading").hide();
                    // var myChanel=res[item][0].bold;
                    // console.log(myChanel);
                    pie_chart("pie_chart_"+item,seriasPieChartData,item.replace('____'," "));
                })
            }
        });
    }
    $(document).on('click', '.groupnameClickability', function() {
        var seriesOptionsPopUp = [];
        $.ajax({
            url: "rankingsJson",
            data: {groupAll:$(this).data('groupname')},
            success: function(res){
                seriesOptionsPopUp[0] = {
                    data: res
                };
                $("#showGraphModalClickability").modal('show');
                // console.log(seriesOptionsPopUp);
                $('#showGraphModalBodyClickability').highcharts('StockChart', {

                    chart: {
                        renderTo: 'showGraphModalBody',
                        type: 'line',
                        // Explicitly tell the width and height of a chart
                        width: 900,
                        height: 500
                    },
                    navigator: {
                        enabled: false
                    },
                    rangeSelector: {
                        inputEnabled:false,
                        allButtonsEnabled: false,
                        buttons: [{
                            type: 'day',
                            count: 7,
                            text: '1 Week',
                            // dataGrouping: {
                            //     forced: true,
                            //     units: [['day', [1]]]
                            // }
                        }, {
                            type: 'week',
                            count: 5,
                            text: '1 Month',
                            // dataGrouping: {
                            //     forced: true,
                            //     units: [['week', [1]]]
                            // }
                        },{
                            type: 'month',
                            count: 6,
                            text: '6 Month',
                            // dataGrouping: {
                            //     forced: true,
                            //     units: [['month', [1]]]
                            // }
                        }, {
                            type: 'all',
                            text: 'All',
                        }],
                        buttonTheme: {
                            width: 60
                        },
                        selected: 0
                    },


                    yAxis: {
                        reversed: true,
                        labels: {
                            formatter: function () {
                                return (this.value > 0 ? ' + ' : '') + this.value;
                            }
                        },
                        plotLines: [{
                            value: 0,
                            width: 2,
                            color: 'silver'
                        }]
                    },

                    plotOptions: {
                        series: {
                            compare: 'value'
                        }
                    },

                    tooltip: {
                        pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({point.change})<br/>',
                        valueDecimals: 2
                    },

                    series: seriesOptionsPopUp
                });
            }
        });
    })


});

function removeKeywordFromGroup(countKeyword,keywords) {
    $(".alert_bootsrap").remove();
    if($('#groups option:selected').val() != '' && $("#actions option:selected").attr('name') == 'removeFromKeywordGroup'){
        $('#removeKeywordFromGroupModal').modal('show');
        $('#removeKeywordFromGroupModal .modal-body').text("Are you saying you want to remove "+ countKeyword +" keywords from "+ $('#groups option:selected').val() +" keyword group?");
        $(".removeKeywordGroupConfirm").click(function () {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-Token': $('input[name="_token"]').val()
                }
            });
            $.ajax({
                method: "POST",
                url: "changeKeywordGroup",
                data: {keyword:keywords,group:$('#groups option:selected').val(),removeKeywordFromGroups:1},
                success: function(res){
                    if(res == 'success'){
                        location.reload();
                    }
                    $('#confirm').modal('hide');
                }
            });
        });


    }else if($("#actions option:selected").attr('name') == 'removeFromKeywordGroup'){
        $('#groups').after(
            '<div class="alert_bootsrap alert alert-danger alert-dismissable">'+
            '<button type="button" class="close" ' +
            'data-dismiss="alert" aria-hidden="true">' +
            '&times;' +
            '</button>' +
            'Please select group' +
            '</div>')
    }else{
        $('#actions').after(
            '<div class="alert_bootsrap alert alert-danger alert-dismissable">'+
            '<button type="button" class="close" ' +
            'data-dismiss="alert" aria-hidden="true">' +
            '&times;' +
            '</button>' +
            'Please select group' +
            '</div>')
    }
}

function pie_chart(chart,seriasPieChartData,chartName){

    $("#"+chart).highcharts('StockChart', {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            useHTML: true,
            text: "<span class='groupnameClickability' data-groupname='"+chartName+"'>"+chartName+"</span>",
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: false,
                cursor: 'pointer',
                marker: {
                    enabled: true
                },
                dataLabels: {
                    enabled: true,
                    useHTML:true,
                    format: '<span class="{point.bold}">{point.name}:{point.percentage:.1f} %</span>',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
                    },
                }
            }
        },
        legend: {
            enabled: true
        },
        navigator: {
            enabled: false
        },
        series: seriasPieChartData
    });
}