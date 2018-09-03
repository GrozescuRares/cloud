$(document).ready(function () {
    $(document).on('change', '.form-control', {}, function (event) {
        searchHotelsByReservationDate(this);
    });

    $(document).on('change', '#appbundle_reservationDto_hotel', {}, function (event) {
       searchRoomsByHotelAndReservationDate($('#appbundle_reservationDto_hotel option:selected'));
    });
});

function searchHotelsByReservationDate(element)
{
    let filed = true;
    $('.form-control').each(function () {
        if (!this.valueAsDate) {
            filed = false;

            return false;
        }
    });

    if (filed) {
        let startDate = $('#appbundle_reservationDto_startDate').val();
        let endDate = $('#appbundle_reservationDto_endDate').val();

        $.ajax({
            url: $('#appbundle_reservationDto_endDate').attr('data-action'),
            type: 'GET',
            dataType: 'html',
            data: {
                'startDate': startDate,
                'endDate' : endDate,
            },
            async: true,

            success: function (data, status) {
                $('#load-hotels').html(data);
                $('#load-rooms').html("");
                $('#appbundle_reservationDto_hotel').selectpicker('refresh');
            },
            error: function (xhr, textStatus, errorThrown) {
                alert('Ajax request failed.')
            }
        });
    }
}

function searchRoomsByHotelAndReservationDate(element)
{
    if ($(element).val() === 'default') {
        $('#load-rooms').html("");

        return false;
    }
    let startDate = $('#appbundle_reservationDto_startDate').val();
    let endDate = $('#appbundle_reservationDto_endDate').val();

    $.ajax({
        url: $('#appbundle_reservationDto_hotel').attr('data-action'),
        type: 'GET',
        dataType: 'html',
        data: {
            'startDate': startDate,
            'endDate' : endDate,
            'hotelId' : $(element).val(),
        },
        async: true,

        success: function (data, status) {
            $('#load-rooms').html(data);
            $('#appbundle_reservationDto_room').selectpicker('refresh');

        },
        error: function (xhr, textStatus, errorThrown) {
            alert('Ajax request failed.')
        }
    });
}
