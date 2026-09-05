<?php
/**
 * AdminLTE Demo Page
 * Renders inside the adminlte layout so you can verify UI chrome (navbar, sidebar, footer)
 * @var \App\View\AppView $this
 */
$this->assign('title', 'Admin Demo');
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-tachometer-alt me-2"></i>AdminLTE Demo Dashboard</h3>
                </div>
                <div class="card-body">
                    <p>Welcome to the AdminLTE demo page. If you can see the top navbar, left sidebar, Font Awesome icons, and this card styled correctly, the AdminLTE layout is applied successfully.</p>

                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="info-box mb-3 bg-info">
                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Users</span>
                                    <span class="info-box-number">128</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box mb-3 bg-success">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Completed Jobs</span>
                                    <span class="info-box-number">54</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box mb-3 bg-warning">
                                <span class="info-box-icon"><i class="fas fa-hourglass-half"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Pending</span>
                                    <span class="info-box-number">7</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-4">Recent Jobs</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Job</th>
                                    <th>Status</th>
                                    <th>Assigned To</th>
                                    <th>Scheduled</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>J-1001</td>
                                    <td>Logo digitizing</td>
                                    <td><span class="badge bg-primary">Digitized</span></td>
                                    <td><i class="fas fa-user me-1"></i>John Doe</td>
                                    <td>2026-08-30</td>
                                </tr>
                                <tr>
                                    <td>J-1002</td>
                                    <td>Uniform embroidery</td>
                                    <td><span class="badge bg-warning">Pending</span></td>
                                    <td><span class="text-muted">-</span></td>
                                    <td>2026-09-03</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
