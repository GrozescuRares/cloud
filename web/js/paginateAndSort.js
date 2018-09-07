$(document).ready(function () {

    $(document).on('click', '.pagination li a', {}, function (event) {
        paginateAndSort(event, this);
    });

    $(document).on('click', 'th.columns', {}, function (event) {
        paginateAndSort(event, this);
    });

    $(document).on('change', '#reservation-hotels', {}, function(event){
        paginateAndSort(event, $('select option:selected'));
    });
});

function paginateAndSort(event, element) {
    event.preventDefault();
    let hotelId = $('#hotels').val();
    let petFilter = $('.table').attr('data-filter-pet');
    let smokingFilter = $('.table').attr('data-filter-smoking');

    $.ajax({
        url: $(element).attr('data-action'),
        type: 'GET',
        dataType: 'html',
        data: {
            'type': $(element).attr('data-role'),
            'pageNumber': $(element).attr('data-page'),
            'column': $(element).attr('data-column'),
            'sort': $(element).attr('data-sort'),
            'hotelId': hotelId,
            'paginate': $(element).attr('data-paginate'),
            'petFilter': petFilter,
            'smokingFilter': smokingFilter,
        },
        success: function (data, status) {
            $('#reload').html(data);
        },
        error: function (xhr, textStatus, errorThrown) {
            alert('Ajax request failed.')
        }
    });
}
