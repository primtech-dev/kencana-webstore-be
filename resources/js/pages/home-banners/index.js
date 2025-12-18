import DataTable from 'datatables.net-bs5';

new DataTable('#bannerTable', {
    processing: true,
    serverSide: true,
    ajax: window.bannerRoutes.index,
    columns: [
        { data: 'DT_RowIndex', orderable: false },
        { data: 'image', orderable: false },
        { data: 'code' },
        { data: 'title' },
        { data: 'sort_order' },
        { data: 'status' },
        { data: 'action', orderable: false }
    ]
});
