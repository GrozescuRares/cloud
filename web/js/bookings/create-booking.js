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

        $.ajax({
            url: $('#appbundle_reservationDto_endDate').attr('data-action'),
            type: 'POST',
            dataType: 'html',
            data: $('form.reservation-form').serialize(),
            success: function (data, status) {
                $('.wrapper').html(data);
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
    $.ajax({
        url: $('#appbundle_reservationDto_hotel').attr('data-action'),
        type: 'POST',
        dataType: 'html',
        data: $('form.reservation-form').serialize(),

        success: function (data, status) {
            $('.wrapper').html(data);
            $('#appbundle_reservationDto_hotel').selectpicker('refresh');
            $('#appbundle_reservationDto_room').selectpicker('refresh');

        },
        error: function (xhr, textStatus, errorThrown) {
            alert('Ajax request failed.')
        }
    });
}
