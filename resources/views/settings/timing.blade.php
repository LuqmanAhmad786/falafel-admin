@extends('layouts.master')

@section('page-css')
@endsection

@section('main-content')
    <div class="row">
        <div class="col-md-12">
            @if($breakfast != null)
                <div class="card p-3 mt-2">
                    <form onsubmit="updateBreakfast();">
                        <h3>Breakfast Time</h3>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Hour</label>
                                    <select class="form-control" id="breakfast_from_hour" required>
                                        <option value="">--</option>
                                        <option value="1" {{$breakfast->from_hour == '01' ? 'selected':''}}>1</option>
                                        <option value="2" {{$breakfast->from_hour == '02' ? 'selected':''}}>2</option>
                                        <option value="3" {{$breakfast->from_hour == '03' ? 'selected':''}}>3</option>
                                        <option value="4" {{$breakfast->from_hour == '04' ? 'selected':''}}>4</option>
                                        <option value="5" {{$breakfast->from_hour == '05' ? 'selected':''}}>5</option>
                                        <option value="6" {{$breakfast->from_hour == '06' ? 'selected':''}}>6</option>
                                        <option value="7" {{$breakfast->from_hour == '07' ? 'selected':''}}>7</option>
                                        <option value="8" {{$breakfast->from_hour == '08' ? 'selected':''}}>8</option>
                                        <option value="9" {{$breakfast->from_hour == '09' ? 'selected':''}}>9</option>
                                        <option value="10" {{$breakfast->from_hour == '10' ? 'selected':''}}>10</option>
                                        <option value="11" {{$breakfast->from_hour == '11' ? 'selected':''}}>11</option>
                                        <option value="12" {{$breakfast->from_hour == '12' ? 'selected':''}}>12</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Minutes</label>
                                    <select class="form-control" id="breakfast_from_minutes" required>
                                        <option value="">--</option>
                                        <option value="00" {{$breakfast->from_minutes == '00' ? 'selected':''}}>00
                                        </option>
                                        <option value="30" {{$breakfast->from_minutes == '30' ? 'selected':''}}>30
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="pt-4">
                                    <i class="i-Arrow-Back-3"></i>
                                    <i class="i-Arrow-Forward-2"></i>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Hour</label>
                                    <select class="form-control" id="breakfast_to_hour" required>
                                        <option value="">--</option>
                                        <option value="1" {{$breakfast->to_hour == '01' ? 'selected':''}}>1</option>
                                        <option value="2" {{$breakfast->to_hour == '02' ? 'selected':''}}>2</option>
                                        <option value="3" {{$breakfast->to_hour == '03' ? 'selected':''}}>3</option>
                                        <option value="4" {{$breakfast->to_hour == '04' ? 'selected':''}}>4</option>
                                        <option value="5" {{$breakfast->to_hour == '05' ? 'selected':''}}>5</option>
                                        <option value="6" {{$breakfast->to_hour == '06' ? 'selected':''}}>6</option>
                                        <option value="7" {{$breakfast->to_hour == '07' ? 'selected':''}}>7</option>
                                        <option value="8" {{$breakfast->to_hour == '08' ? 'selected':''}}>8</option>
                                        <option value="9" {{$breakfast->to_hour == '09' ? 'selected':''}}>9</option>
                                        <option value="10" {{$breakfast->to_hour == '10' ? 'selected':''}}>10</option>
                                        <option value="11" {{$breakfast->to_hour == '11' ? 'selected':''}}>11</option>
                                        <option value="12" {{$breakfast->to_hour == '12' ? 'selected':''}}>12</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Minutes</label>
                                    <select class="form-control" id="breakfast_to_minutes" required>
                                        <option value="">--</option>
                                        <option value="00" {{$breakfast->to_minutes == '00' ? 'selected':''}}>00
                                        </option>
                                        <option value="30" {{$breakfast->to_minutes == '30' ? 'selected':''}}>30
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 text-center mt-4">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
            @if($lunch != null)
                <div class="card p-3 mt-2">
                    <form onsubmit="updateLaunch();">
                        <h3>Launch Time</h3>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Hour</label>
                                    <select class="form-control" id="lunch_from_hour" required>
                                        <option value="">--</option>
                                        <option value="1" {{$lunch->from_hour == '01' ? 'selected':''}}>1</option>
                                        <option value="2" {{$lunch->from_hour == '02' ? 'selected':''}}>2</option>
                                        <option value="3" {{$lunch->from_hour == '03' ? 'selected':''}}>3</option>
                                        <option value="4" {{$lunch->from_hour == '04' ? 'selected':''}}>4</option>
                                        <option value="5" {{$lunch->from_hour == '05' ? 'selected':''}}>5</option>
                                        <option value="6" {{$lunch->from_hour == '06' ? 'selected':''}}>6</option>
                                        <option value="7" {{$lunch->from_hour == '07' ? 'selected':''}}>7</option>
                                        <option value="8" {{$lunch->from_hour == '08' ? 'selected':''}}>8</option>
                                        <option value="9" {{$lunch->from_hour == '09' ? 'selected':''}}>9</option>
                                        <option value="11" {{$lunch->from_hour == '11' ? 'selected':''}}>11</option>
                                        <option value="12" {{$lunch->from_hour == '12' ? 'selected':''}}>12</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Minutes</label>
                                    <select class="form-control" id="lunch_from_minutes" required>
                                        <option value="">--</option>
                                        <option value="00" {{$lunch->from_minutes == '00' ? 'selected':''}}>00</option>
                                        <option value="30" {{$lunch->from_minutes == '30' ? 'selected':''}}>30</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="pt-4">
                                    <i class="i-Arrow-Back-3"></i>
                                    <i class="i-Arrow-Forward-2"></i>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Hour</label>
                                    <select class="form-control" id="lunch_to_hour" required>
                                        <option value="">--</option>
                                        <option value="1" {{$lunch->to_hour == '01' ? 'selected':''}}>1</option>
                                        <option value="2" {{$lunch->to_hour == '02' ? 'selected':''}}>2</option>
                                        <option value="3" {{$lunch->to_hour == '03' ? 'selected':''}}>3</option>
                                        <option value="4" {{$lunch->to_hour == '04' ? 'selected':''}}>4</option>
                                        <option value="5" {{$lunch->to_hour == '05' ? 'selected':''}}>5</option>
                                        <option value="6" {{$lunch->to_hour == '06' ? 'selected':''}}>6</option>
                                        <option value="7" {{$lunch->to_hour == '07' ? 'selected':''}}>7</option>
                                        <option value="8" {{$lunch->to_hour == '08' ? 'selected':''}}>8</option>
                                        <option value="9" {{$lunch->to_hour == '09' ? 'selected':''}}>9</option>
                                        <option value="11" {{$lunch->to_hour == '11' ? 'selected':''}}>11</option>
                                        <option value="12" {{$lunch->to_hour == '12' ? 'selected':''}}>12</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Minutes</label>
                                    <select class="form-control" id="lunch_to_minutes" required>
                                        <option value="">--</option>
                                        <option value="00" {{$lunch->to_minutes == '00' ? 'selected':''}}>00</option>
                                        <option value="30" {{$lunch->to_minutes == '30' ? 'selected':''}}>30</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 text-center mt-4">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('page-js')
@endsection

@section('bottom-js')
    <script>
        $(document).ready(function () {
            $('form').on('submit', function (e) {
                e.preventDefault();
            })
        });

        var $breakfastFromHour = $('#breakfast_from_hour');
        var $breakfastFromMinutes = $('#breakfast_from_minutes');
        var $breakfastToHour = $('#breakfast_to_hour');
        var $breakfastToMinutes = $('#breakfast_to_minutes');

        var $lunchFromHour = $('#lunch_from_hour');
        var $lunchFromMinutes = $('#lunch_from_minutes');
        var $lunchToHour = $('#lunch_to_hour');
        var $lunchToMinutes = $('#lunch_to_minutes');

        function updateBreakfast() {
            if (!$breakfastFromHour.val() || !$breakfastFromMinutes.val() || !$breakfastToHour.val() || !$breakfastToMinutes.val()) {
                toastr.error("Please fill all required data", "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                return false;
            }

            var data = {
                from_hour: $breakfastFromHour.val(),
                from_minutes: $breakfastFromMinutes.val(),
                to_hour: $breakfastToHour.val(),
                to_minutes: $breakfastToMinutes.val(),
                type: 1
            };

            updateTime(data);
        }

        function updateLaunch() {
            if (!$lunchFromHour.val() || !$lunchFromMinutes.val() || !$lunchToHour.val() || !$lunchToMinutes.val()) {
                toastr.error("Please fill all required data", "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                return false;
            }

            var data = {
                from_hour: $lunchFromHour.val(),
                from_minutes: $lunchFromMinutes.val(),
                to_hour: $lunchToHour.val(),
                to_minutes: $lunchToMinutes.val(),
                type: 2
            };

            updateTime(data);
        }

        function updateTime(data) {
            var fromTime = (parseInt(data.from_hour) * 60) + parseInt(data.from_minutes);
            var toTime = (parseInt(data.to_hour) * 60) + parseInt(data.to_minutes);

            if (fromTime >= toTime) {
                toastr.error("From time can't be equal or greater than To", "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
                return false;
            }

            axios.post(baseUrl + 'update-time', data).then(function () {
                toastr.success("Time updated successfully", "Success", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                });
            }).catch(function (error) {
                toastr.error(error.response.data.message, "Required!", {
                    timeOut: "3000",
                    positionClass: "toast-bottom-right"
                })
            });
        }
    </script>
@endsection
