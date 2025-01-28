@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalContent">
                    <!-- Content will be dynamically injected -->
                </div>
            </div>
        </div>
    </div>
</div>
    <table border="1" class="table table-bordered" id="myTable">
        <thead>
        <tr>
            <th scope="col">IP</th>
            <th scope="col">Uid</th>
            <th scope="col">DateTime</th>
            <th scope="col">Type</th>
            <th scope="col"># of files</th>
            <th scope="col">Content</th>
            <th scope="col">Block Status</th>
            <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($settings as $sett)
            <tr>
                <td>{{$sett->ip}}</td>
                <td>{{$sett->uid}}</td>
                <td>{{$sett->created_at}}</td>
                <td>{{$sett->typeintext}}</td>
                    @if ($sett->type == 2)
                    <td>{{count($sett->content)}}</td>
                    @else
                    <td>0</td>
                    @endif
                    <td width= "500px">
                        {{--
                        @foreach ($sett->content as $c)
                        @if ($sett->type == 1)
                        Text: {{$c['content']}}<br>
                        @elseif ($sett->type == 2)
                        File: <a href="{{ route('fileurl',['fileurl' => $c['id']]) }}">{{$c['file_detail']}}</a><br>
                        @endif
                        @endforeach
                        --}}
                        <button 
                        class="btn btn-primary btn-sm detailview" 
                        my-data="{{$sett->id}}"
                        data-bs-toggle="modal" 
                        data-bs-target="#detailModal" 
                        data-content="{{ json_encode($sett->content) }}" 
                        data-type="{{ $sett->type }}"
                        >
                        Check Details
                        </button><br>
                    </td>
                    @if ($sett->block)
                    <td>Blocked</td>
                    @else
                    <td>Open</td>
                    @endif
                <td>
                    <form method="post" action="{{route('blocker', ['blockid' => $sett])}}">
                        @csrf
                        @if ($sett->block)
                        <input class="btn btn-primary" type="submit" value="UnBlock" />
                        @else
                        <input class="btn btn-danger" type="submit" value="Block" />
                        @endif
                        
                    </form>
                </td>
            </tr>
            {{--<tr><td></td>
                <td width= "500px">
                    @foreach ($sett->content as $c)
                    @if ($sett->type == 1)
                    Text: {{$c['content']}}<br>
                    @elseif ($sett->type == 2)
                    File: <a href="{{ route('fileurl',['fileurl' => $c['id']]) }}">{{$c['file_detail']}}</a><br>
                    @endif
                    @endforeach
                </td>
            </tr>--}}
        @endforeach
        </tbody>
    </table>
    {{-- <p>{{phpinfo()}}</p> --}}
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="//cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .icon-square {
            display: inline-block;
            width: 80px;
            height: 80px;
            border-radius: 8px;
            background-color: #6c757d;
            color: white;
            text-align: center;
            line-height: 80px;
        }
    
        .img-thumbnail {
            object-fit: cover;
            border: 1px solid #ddd;
        }
    
        .small.text-truncate {
            max-width: 80px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js"></script>
    <script> console.log("Hi, I'm using the Laravel-AdminLTE package!"); </script>
    <script src="//cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script
			  src="https://code.jquery.com/jquery-3.7.1.slim.js"
			  integrity="sha256-UgvvN8vBkgO0luPSUl2s8TIlOSYRoGFAX4jlCIm9Adc="
			  crossorigin="anonymous"></script>
    <script>
        let table = new DataTable('#myTable',{
    pageLength: 6
});
    </script>
    <script>
        const modalContent = document.getElementById("modalContent");
            document.querySelector("#myTable").addEventListener("click", function (event) {
                // Check if the clicked element is a detail view button
                if (event.target && event.target.classList.contains("detailview")) {
                    console.log("Detail view button clicked!");
                    const button = event.target;

                    //document.addEventListener("DOMContentLoaded", () => {
                    //const detailButtons = document.querySelectorAll(".detailview");


                    //detailButtons.forEach((button) => {
                    //button.addEventListener("click", () => {
                    const content = JSON.parse(button.getAttribute("data-content"));
                    const type = button.getAttribute("data-type");
                    const id = button.getAttribute("my-data");
                    // Clear previous content
                    modalContent.innerHTML = "";

                    if (type == 1) {
                        modalContent.innerHTML += `<p>Text:</p><hr>`;
                        // Display text content
                        content.forEach((item) => {
                            modalContent.innerHTML += `<p> ${item.content}</p>`;
                        });
                    } else if (type == 2)
                        // {
                        //     // Display file links
                        //     content.forEach((item) => {
                        //         modalContent.innerHTML += `
                        //             <p>File: <a href="${item.file_detail}" target="_blank">${item.file_detail}</a></p>
                        //         `;
                        //     });
                        // }

                        if (type == 2) {
                            modalContent.innerHTML += `<p>File(s):</p>`;
                            // For files, display icons based on type
                            content.forEach((file) => {
                                const fileUrl = file.file_detail; // File URL
                                const fileThumbnail = file.thumbnail
                                const fileId = file.id;
                                const fileName = fileUrl.split("/").pop(); // Extract file name
                                const fileExtension = fileName.split(".").pop().toLowerCase(); // Extract file extension
                                const fileUID = file.file_uid

                                // Check if it's an image file
                                //const isImage = ["jpg", "jpeg", "png", "gif", "webp"].includes(fileExtension);
                                const isMedia = ["jpg", "jpeg", "png", "gif", "webp", "mp4", "mkv", "avi", "mov", "webm"].includes(fileExtension);
                                if (isMedia) {
                                    // Display image thumbnail
                                    modalContent.innerHTML += `
                            <div class="d-inline-block text-center m-2">
                                
                                <a href="/download/${fileId}" target="_blank">
                                    <img src="/storage/${fileThumbnail}" alt="${fileUID}" class="img-thumbnail" style="width: 80px; height: 80px;">
                                </a>
                                <p class="small">UID:<br> ${fileUID}</p>
                            </div>
                        `;
                                } else {
                                    // Display generic file icon
                                    // <p class="small text-truncate">UID: ${fileUID}</p>
                                    modalContent.innerHTML += `
                            <div class="d-inline-block text-center m-2">
                                <a href="/download/${fileId}" target="_blank">
                                    <div class="icon-square bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 24px; border-radius: 8px;">
                                        <i class="bi bi-file-earmark"></i>
                                    </div>
                                </a>
                                <p class="small">UID:<br> ${fileUID}</p>
                            </div>
                        `;
                                }
                            });
                        }
        //});
    //});
//});
}
});
    </script>
@stop