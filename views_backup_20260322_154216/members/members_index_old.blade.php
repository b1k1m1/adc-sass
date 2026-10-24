@extends('admin.admin_dashboard')
@section('admin')

<style>

    /* AdminLTE-like info-box with border */
    .opc_cus{
        opacity: 1;
    }
    .info-box {
      display: flex;
      align-items: center;
      padding: 0.75rem;
      background: white;
      /*/border: 1px solid #dee2e6; /* Bootstrap's default border color */
      border: 1px solid lightslategray;
      border-radius: 0.5rem; /* Slightly rounded corners */
      box-shadow: 0 1px 1px rgba(0,0,0,0.05);
      transition: all 0.3s ease;
      cursor: pointer;
      background-color: snow;
    }
    .info-box:hover {
      transform: translateY(-3px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      border-color: #adb5bd; /* Darker border on hover */
    }
    .info-box:hover .info-box-text,
    .info-box:hover .info-box-number {
      color: #fd0d0d; /* Bootstrap primary color */
    }
    .info-box-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 50px;
      height: 50px;
      border-radius: 0.4rem; /* Slightly rounded icon background */
      margin-right: 1rem;
      font-size: 1.5rem;
      color: white;
    }
    .info-box-content {
      flex: 1;
    }
    .info-box-text {
      display: block;
      font-size: 0.9rem;
      color: #6c757d; /* Bootstrap secondary color */
    }
    .info-box-number {
      display: block;
      font-weight: bold;
      font-size: 1.2rem;
      color: #343a40; /* Bootstrap dark color */
    }

  </style>
<div class="page-content">
    <div class="container-fluid">


        <!-- start page title>
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Tabs & Accordions</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Components</a></li>
                            <li class="breadcrumb-item active">Tabs & Accordions</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
       end page title -->

        <!--
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                         Nav tabs -->
                         <!--
                        <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#home1" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                    <span class="d-none d-sm-block">Data Entry</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#profile1" role="tab">
                                    <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                    <span class="d-none d-sm-block">Reports</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#messages1" role="tab">
                                    <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                    <span class="d-none d-sm-block">Settings</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#settings1" role="tab">
                                    <span class="d-block d-sm-none"><i class="fas fa-cog"></i></span>
                                    <span class="d-none d-sm-block">Help</span>
                                </a>
                            </li>
                        </ul>
                    -->
                        <!-- Tab panes
                        <div class="tab-content p-3 text-muted">
                            <div class="tab-pane active" id="home1" role="tabpanel">

                                <div>
                                    <a href="{{ route('all.members') }}"><button type="button" class="btn btn-soft-light waves-effect">Members Information</button></a>
                                    <a href="{{ route('add.member') }}"><button type="button" class="btn btn-soft-light waves-effect">Add Members</button></a>
                                </div>

                            </div>

                            <div class="tab-pane" id="profile1" role="tabpanel">
                                <p class="mb-0">
                                    Food truck fixie locavore, accusamus mcsweeney's marfa nulla
                                    single-origin coffee squid. Exercitation +1 labore velit, blog
                                    sartorial PBR leggings next level wes anderson artisan four loko
                                    farm-to-table craft beer twee. Qui photo booth letterpress,
                                    commodo enim craft beer mlkshk aliquip jean shorts ullamco ad
                                    vinyl cillum PBR. Homo nostrud organic, assumenda labore
                                    aesthetic magna delectus.
                                </p>
                            </div>
                            <div class="tab-pane" id="messages1" role="tabpanel">
                                <p class="mb-0">
                                    Etsy mixtape wayfarers, ethical wes anderson tofu before they
                                    sold out mcsweeney's organic lomo retro fanny pack lo-fi
                                    farm-to-table readymade. Messenger bag gentrify pitchfork
                                    tattooed craft beer, iphone skateboard locavore carles etsy
                                    salvia banksy hoodie helvetica. DIY synth PBR banksy irony.
                                    Leggings gentrify squid 8-bit cred pitchfork. Williamsburg banh
                                    mi whatever gluten-free carles.
                                </p>
                            </div>
                            <div class="tab-pane" id="settings1" role="tabpanel">
                                <p class="mb-0">
                                    Trust fund seitan letterpress, keytar raw denim keffiyeh etsy
                                    art party before they sold out master cleanse gluten-free squid
                                    scenester freegan cosby sweater. Fanny pack portland seitan DIY,
                                    art party locavore wolf cliche high life echo park Austin. Cred
                                    vinyl keffiyeh DIY salvia PBR, banh mi before they sold out
                                    farm-to-table VHS viral locavore cosby sweater. Lomo wolf viral,
                                    mustache readymade keffiyeh craft.
                                </p>
                            </div>
                        </div>
                    </div>end card-body -->
                    <!--
                </div> end card
            </div><end col
        </div> end row


    </div>
    <container-fluid
</div>
-->
<!-- End Page-content
-->



    <!-- Clickable Info Box (AdminLTE Style) -->
    <div class="row">
         <div class="col-md-2 col-sm-6 col-xs-12">
    <a href="https://example.com/messages" class="text-decoration-none">
      <div class="info-box mb-4">
        <span class="info-box-icon bg-info">
          <i class="bx bx-clinic"></i>
        </span>
        <div class="info-box-content">
          <span class="info-box-text">Messages</span>
          <span class="info-box-number">1,410</span>
        </div>
      </div>
    </a>
         </div>

    <!-- Example with Different Color -->
     <div class="col-md-2 col-sm-6 col-xs-12">
    <a href="https://example.com/sales" class="text-decoration-none">
      <div class="info-box mb-4">
        <span class="info-box-icon bg-success">
          <i class="fas fa-dollar-sign"></i>
        </span>
        <div class="info-box-content">
          <span class="info-box-text">Sales</span>
          <span class="info-box-number">$12,345</span>
        </div>
      </div>
    </a>
     </div>
     <div class="col-md-2 col-sm-6 col-xs-12">
        <a href="https://example.com/messages" class="text-decoration-none">
        <div class="info-box mb-4">
          <span class="info-box-icon bg-info">
            <i class="dripicons-contract-2"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Messages</span>
            <span class="info-box-number">1,410</span>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12">
      <a href="https://example.com/messages" class="text-decoration-none">
        <div class="info-box mb-4">
          <span class="info-box-icon bg-info">
            <i class="bx bx-gas-pump"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Messages</span>
            <span class="info-box-number">1,410</span>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12 opc_cus">
      <a href="https://example.com/messages" class="text-decoration-none">
        <div class="info-box mb-4">
          <span class="info-box-icon bg-info">
            <i class="far fa-envelope"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Messages</span>
            <span class="info-box-number">1,410</span>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-2 col-sm-6 col-xs-12 opc_cus">
      <a href="https://example.com/messages" class="text-decoration-none">
        <div class="info-box mb-4">
          <span class="info-box-icon bg-info">
            <i class="far fa-envelope"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Messages</span>
            <span class="info-box-number">1,410</span>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12 opc_cus">
      <a href="https://example.com/messages" class="text-decoration-none">
        <div class="info-box mb-4">
          <span class="info-box-icon bg-info">
            <i class="far fa-envelope"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Messages</span>
            <span class="info-box-number">1,410</span>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12 opc_cus">
      <a href="https://example.com/messages" class="text-decoration-none">
        <div class="info-box mb-4">
          <span class="info-box-icon bg-warning">
            <i class="fas fa-archway"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Messages</span>
            <span class="info-box-number">1,410</span>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12 opc_cus">
      <a href="https://example.com/messages" class="text-decoration-none">
        <div class="info-box mb-4">
          <span class="info-box-icon bg-info">
            <i class="far fa-envelope"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Messages</span>
          </div>
        </div>
      </a>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12">
    <a href="https://example.com/messages" class="text-decoration-none">
        <div class="info-box mb-4">
            <span class="info-box-icon bg-info"><i class="far fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-number"> Testing </span>
            </div>
        </div>
    </a>
    </div>
    <div class="col-md-4 col-sm-6 col-xs-12 opc_cus">
        <a href="https://example.com/messages" class="text-decoration-none">
          <div class="info-box mb-4">
            <span class="info-box-icon bg-danger">
              <i class="far fa-clock"></i>
            </span>
            <div class="info-box-content">
              <span class="info-box-text">Messages</span>
            </div>
          </div>
        </a>
      </div>
 </div>
@endsection

