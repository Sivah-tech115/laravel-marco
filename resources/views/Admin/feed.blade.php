@extends('Admin.layouts.main')

@section('breadcrumbtitle', 'Feeds')
@section('breadcrumbtitle2', 'Feed Link')
@section('content')

@section('styles')
<style>
    .copy-container {
    background-color: #e6f4ea;
    padding: 15px;
    border-left: 4px solid #28a745;
    border-radius: 6px;
    font-size: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.copy-url {
    overflow-wrap: anywhere;
    color: #155724;
    font-weight: 500;
    flex-grow: 1;
}

.copy-btn {
    background-color: #28a745;
    color: #fff;
    border: none;
    padding: 6px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.copy-btn:hover {
    background-color: #218838;
}
</style>
@endsection

<div class="row">

    <!-- Form Card -->
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5>Product Feed Link</h5>
                <p>Copy the following Product Feed link </p>
            </div>
            <div class="card-body">
            <div class="copy-container">
                <span id="feedUrl" class="copy-url">{{ $feedUrl }}</span>
                <a href="javascript:void(0)" onclick="copyToClipboard()">Copy Link</a>
                <span id="copyStatus" style="margin-left: 10px;"></span>
            </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function copyToClipboard() {
    const url = document.getElementById("feedUrl").textContent;
    navigator.clipboard.writeText(url).then(() => {
        document.getElementById("copyStatus").textContent = "✔️ Link copied to clipboard.";
        document.getElementById("copyStatus").style.color = "green";
    }).catch(err => {
        document.getElementById("copyStatus").textContent = "❌ Failed to copy link.";
        document.getElementById("copyStatus").style.color = "red";
        console.error("Failed to copy: ", err);
    });
}
</script>
@endsection