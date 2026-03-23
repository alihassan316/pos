@extends('layouts.pos')

@section('title', 'Settings')

@section('content')



<div class="row g-4">
   
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-receipt me-2 text-primary"></i>Shortcut Keys</h5>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Key</th>
                            <th>Fuction</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                        	<td colspan="2"><b>All Pages</b></td>
                        </tr>
                        <tr>
                        	<td>Ctrl + U</td>
                            <td>Open Sale Page</td>
                        </tr>
                        <tr>
                        	<td>Ctrl + Shift + U</td>
                            <td>Open New Sale Page</td>
                        </tr>
                        <tr>
                        	<td colspan="2">Sale Page</td>
                        </tr>
                        <tr>
                        	<td>Ctrl + Z</td>
                            <td>Add New Item</td>
                        </tr>
                        <tr>
                        	<td>Ctrl + X</td>
                            <td>Remove New Item</td>
                        </tr>
                        <tr>
                        	<td>Ctrl + S</td>
                            <td>Save Sale</td>
                        </tr>
                        <tr>
                        	<td>Ctrl + P</td>
                            <td>Save Sale & Print</td>
                        </tr>
                        <tr>
                        	<td colspan="2">Overall
                           </td>
                         </tr>
                         <tr>
                         	<td>Ctrl + Tab</td>
                            <td>Switch between opened Tabs</td>
                         </tr>
                        
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection
