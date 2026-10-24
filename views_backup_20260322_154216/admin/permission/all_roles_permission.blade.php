@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="card-head">
                            <h5 class="card-title">All Roles Permission</h5>
                            <table id="datatable" class="table card-table">
                                <thead>
                                  <tr>
                                      <th>Sl</th>
                                      <th>Role Name</th>
                                      <th>Permission</th>
                                      <th>Action</th>
                                  </tr>
                                </thead>
                                <tbody class="table-border-bottom-0">
                                  @foreach ($roles as $key => $item )
                                      <tr>
                                          <td>{{ $key+1 }}</td>
                                          <td>{{ $item->name }}</td>
                                          <td>
                                              @foreach ($item->permissions as $perm)
                                                  <span class="badge rounded-pill bg-danger bg-glow">{{ $perm->name }}</span>
                                              @endforeach
                                          </td>
                                          <td> <a href="{{ route('edit.roles.permission', $item->id) }}" class="btn btn-round btn-sm btn-warning" id="edit"> Edit </a>
                                            <a href="{{ route('delete.roles.permission', $item->id) }}" class="btn btn-round btn-sm btn-danger" id="delete"> Delete </a>
                                          </td>
                                      </tr>
                                  @endforeach
                                </tbody>
                              </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
