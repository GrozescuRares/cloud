var hms = {};
/* SHOW DROPDOWN MENU ON HOVER
 -------------------------------*/
hms.menuDropdownHover = function () {
    $('.dropdown').hover(function () {
        $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeIn("fast");
    }, function () {
        $(this).find('.dropdown-menu').stop(true, true).delay(200).fadeOut("fast");
    });
};
/* GENERATE TABS
 -------------------------------*/
hms.burgerMenu = function () {
    $('#nav-icon1,#nav-icon2').click(function () {
        $(this).toggleClass('open');
    });
};

/* Custom Selectpicker
 -------------------------------*/
hms.customSelect = function () {
    $('.selectpicker').selectpicker({
        style: 'btn-info'
    });
};

/* Datepicker
 -------------------------------*/
hms.customDatepicker = function () {
    $('.date-input input').datepicker({
        format: 'mm/dd/yyyy',
        startDate: '0d'
    });
};

/* Validate login Form
 -------------------------------*/
//hms.validateForm = function () {
//    $(".login-form").validate({
//        rules: {
//            username: "required",
//            pass: {
//                required: true,
//                minlength: 5
//            }
//        },
//        messages: {
//            username: "Please provide username",
//            pass: {
//                required: "Please provide password",
//                minlength: "Password is too short"
//            }
//        },
//        submitHandler: function (form) {
//            form.submit();
//        },
//        highlight: function (element) {
//            $(element).parent().addClass("field-error");
//        },
//        success: function (element) {
//            $(element).parent().removeClass("field-error");
//        },
//        errorPlacement:
//                function (error, element) {
//                    error.insertAfter(element.parent());
//                }
//    });
//};

/* Validate login Form
 -------------------------------*/
hms.validateForm = function () {
    $.validator.addMethod("valueNotEquals", function (value, element, arg) {
        return arg !== value;
    }, "Value must not equal arg.");

    $("form").each(function () {
        $(this).validate({
            rules: {
                username: "required",
                email: "required",
                pass: {
                    required: true,
                    minlength: 5
                },
                confirmPass: {
                    required: true,
                    equalTo: '#pass'
                },
                role: {
                    valueNotEquals: "default"
                },
                hotel: {
                    valueNotEquals: "default"
                },
                roomCapacity: {
                    valueNotEquals: "default"
                },
                selectRoom: {
                    valueNotEquals: "default"
                },
                gender: {
                    required: true
                },
                smoking: {
                    required: true
                },
                price: {
                    required: true
                },
                startDate: {
                    required: true
                },
                endDate: {
                    required: true
                }
            },
            messages: {
                username: "Please provide username.",
                email: "Please provide a valid email address.",
                pass: {
                    required: "Please provide password.",
                    minlength: "Password is too short."
                },
                confirmPass: {
                    required: "This field is required."
                },
                role: "Please select a Role.",
                hotel: "Please select a Hotel.",
                roomCapacity: "Please add room capacity.",
                selectRoom: "Please select Room.",
                gender: "Please choose gender.",
                smoking: "Please choose smoking option.",
                price: "Please add price.",
                startDate: "Add start date.",
                endDate: "Add end date."
            },
            submitHandler: function (form) {
                form.submit();
            },
            highlight: function (element) {
                $(element).parent().addClass("field-error");
            },
            success: function (element) {
                $(element).parent().removeClass("field-error");
            },
            errorPlacement:
                    function (error, element) {
                        if ((element.attr("name") == "gender") || (element.attr("name") == "smiking")) {
                            error.insertAfter(element.parent().parent());
                        } else {
                            error.insertAfter(element.parent());
                        }
                    }
        });
    });
};

/* Open Filters for specific tab
 -------------------------------*/
hms.openFilters = function () {
    $(".filters-toggle").on("click", function (event) {
        event.preventDefault();
        $(this).toggleClass("open").next(".filters-open").slideToggle("fast");
    });
};

/* Toggle Sorting Icon color on click
 -------------------------------*/
hms.toggleSortIcon = function () {
    $(".table-responsive th").on("click", function () {
        if ($(this).find(".sort-icon").hasClass("asc")) {
            $(this).find(".sort-icon").removeClass("asc").addClass("desc");
        } else if ($(this).find(".sort-icon").hasClass("desc")) {
            $(this).find(".sort-icon").removeClass("desc");
        } else {
            $(this).find(".sort-icon").addClass("asc");
        }
    });
};

/* Test login inputs to see if they have value
 -------------------------------*/
hms.testLoginInputs = function () {
    let
    isWebkit
            = 'WebkitAppearance' in document.documentElement.style;
    $(".login-wrapper input").on('change', function () {
        $(".login-wrapper input").each(function () {
            $(this).parent().removeClass('field-error');
            $(this).attr('aria-invalid', false);
            $(this).parent().find('.error').hide();
        });
    });
    if (isWebkit && $('input:-webkit-autofill')) {
        $('input:-webkit-autofill').each(function () {
            $(this).addClass("has-value");
        });
        $('input:-webkit-autofill').on('change', function () {
            $(this).removeClass('has-value');
        });
    } else {
        $(".login-wrapper input").each(function (item) {
            if ($(this).html() !== '') {
                $(this).addClass("has-value");
            } else {
                $(this).removeClass("has-value");
            }
        });
    }
};

/* Show edit buttons and enable inputs
 -------------------------------*/
hms.enableEdit = function () {
    $(".banner-actions .edit").on("click", function (item) {
        $(this).parent().toggleClass("active");
        if ($(".edit-btn-wrapper").hasClass("hidden")) {
            $(".wrapper .general-info .selectpicker").prop("disabled", false).selectpicker('refresh');
            $(".wrapper .dropzone").removeClass("dz-max-files-reached");
            $(".rangeslider--horizontal").removeClass("rangeslider--disabled");
            $(".wrapper .general-info .input-wrapper input, .wrapper .approver-information input, .wrapper textarea, .wrapper .tab-pane > .col-md-12 .autocomplete input").prop('disabled', false);
        } else {
            $(".wrapper .general-info .selectpicker").prop("disabled", true).selectpicker('refresh');
            $(".wrapper .dropzone").addClass("dz-max-files-reached");
            $(".rangeslider--horizontal").addClass("rangeslider--disabled");
            $(".wrapper .general-info .input-wrapper input, .wrapper .approver-information input, .wrapper textarea, .wrapper .tab-pane > .col-md-12 .autocomplete input").prop('disabled', true);
        }
        $(".edit-btn-wrapper").toggleClass("hidden");

    });

    $(".edit-btn-wrapper .cancel").on("click", function (event) {
        event.preventDefault();
        $(".banner-actions .edit").parent().removeClass("active");
        $(".wrapper .general-info .selectpicker").prop("disabled", true).selectpicker('refresh');
        $(".wrapper .dropzone").addClass("dz-max-files-reached");
        $(".rangeslider--horizontal").addClass("rangeslider--disabled");
        $(".wrapper .general-info .input-wrapper input, .wrapper .approver-information input, .wrapper textarea, .wrapper .tab-pane > .col-md-12 .autocomplete input").prop('disabled', true);
        $(".edit-btn-wrapper").addClass("hidden");
    });
};

hms.removeDocument = function (element) {
    $(".remove-item").click(function () {
        $(this).parent().remove();
    });
};

/* DOCUMENT READY
 -------------------------------*/
$(document).ready(function () {
    setTimeout(function () {
        hms.testLoginInputs();
    }, 1300);

    hms.menuDropdownHover();
    hms.burgerMenu();
    hms.customSelect();
    hms.customDatepicker();
    hms.removeDocument();
    hms.validateForm();
    hms.openFilters();
    hms.toggleSortIcon();
    hms.enableEdit();
});

/* WINDOW LOAD
 -------------------------------*/
$(window).load(function () {
    hms.customSelect();
});
