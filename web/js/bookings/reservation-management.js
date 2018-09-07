$(document).ready(function () {
    $(document).on('click', '.delete-reservation', {}, function (event) {
        deleteReservation(event, this);
    });
});

function deleteReservation(event, element)
{
    console.log(element);
}
