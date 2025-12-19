import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';

$(function () {
    new DataTable('#reviewsTable', {
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: window.reviewRoutes.index,
        columns: [
            { data: 'DT_RowIndex', orderable:false },
            { data: 'product.name', defaultContent:'-' },
            { data: 'customer.name', defaultContent:'-' },
            { data: 'rating', orderable:false },
            { data: 'status', orderable:false },
            { data: 'created_at' },
            { data: 'action', orderable:false }
        ]
    });
});
