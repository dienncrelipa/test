@extends('webapp/layout')

@section('addonScript')
    {!! HTML::style('res/css/select2.min.css?v=20170215') !!}
    {!! HTML::style('res/css/select2-bootstrap.min.css?v=20170215') !!}
    {!! HTML::script('res/js/select2.full.min.js?v=20170215') !!}
    {!! HTML::script('res/js/cms-filter.js?v=20170918') !!}
    <style>
        .input-sm {
            padding: 0;
        }
        .form-control, .btn, ul.ui-corner-all{
            border-radius: 0px;
        }
        span.select2-selection__clear{
            width: 100%;
            height: 100%;
            opacity: 0;
            padding: 0;
            margin: 0;
        }
        span.select2-container{
            width: 100% !important;
        }
        span.select2-selection,
        span.select2-dropdown{
            border-radius: 0 !important;
        }
        span.select2-selection__placeholder{
            color: #555 !important;
        }
        .select2-container--bootstrap .select2-selection--single .select2-selection__arrow b{
            border-color: #555 transparent transparent;
        }
        td.column-published_status select,
        td.column-title input{
            height: 34px;
            line-height: 34px;
        }
        .select2-container--bootstrap .select2-results__option{
            word-break: break-all;
        }
        th.column-id{
            width: 100px;
        }
        .select2-container--bootstrap .select2-search--dropdown .select2-search__field{
            margin-bottom: 0px;
        }
        thead td.column-message select#filter-activity-log{
            width: 100%;
            margin-left: 20px;
        }
        thead td.column-message {
            display: flex;
            height: 50%;
        }
    </style>
    <script type='text/javascript' src='{{ asset('res/js/jquery.confirm.min.js') }}'></script>
    <script src='{{ asset('res/js/jquery-ui.min.js') }}' type="text/javascript" charset="utf-8"></script>
    <script type='text/javascript' src='{{ asset('res/js/tag-it.min.js') }}'></script>
@stop
@section('mainContent')
    @include('webapp/components/notification')
    <div class="row">
        <div class="table-responsive">
            {!! $grid !!}
        </div>
    </div>
    <script>

        $(document).ready(function($){

            $.each($("tbody td.column-time"), function(k ,e) {
                var time = $(this).text();
                $(e).html(moment.utc(time).toDate());
            });

            // Change 'form' to class or ID of your specific form
            $("form").submit(function() {
                $(this).find(":input").filter(
                    function() {
                        return !this.value;
                    }).attr("disabled", "disabled");
                return true; // ensure form still submits
            });
            // Un-disable form fields when page loads, in case they click back after submission
            $( "form" ).find( ":input" ).prop( "disabled", false );
            addLogFilter({{ $is_filter }});
        });

    </script>
@endsection