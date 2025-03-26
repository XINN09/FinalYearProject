<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents - {{ $project->projectName }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/document.css') }}">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>

<body>
    <div class="main-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            @include('generalComponent.sidebar')
        </aside>

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Navigation -->
            <header class="navigation">
                @include('generalComponent.navigation')
            </header>

            <!-- Page Content -->
            <div class="content">
                <h1 style="display: flex; flex-direction: column; align-items: flex-start; gap: 4px;">
                    <div class="tag-header" style="display: flex;">
                        <span style="font-size: 13px; color: #ffffff; background-color: #f0990e; padding: 2px 15px; font-weight: normal; border-radius: 15px; margin: 0 10px;">Project</span>
                        <span style="font-size: 13px; color: #ffffff; background-color: #45a6eb; padding: 2px 15px; font-weight: normal; border-radius: 15px; margin: 0 10px;">
                            {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('Y-m-d') : 'N/A' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('Y-m-d') : 'N/A' }}
                        </span>
                    </div>
                    <div class="project-top" onclick="toggleProjectInfo()">
                        <span>{{ $project->projectName }}</span>
                        <button class="toggle-info-btn">
                            <i id="projectInfoIcon" class="fa fa-angle-down"></i>
                        </button>
                    </div>
                </h1>


                <div class="project-info-panel" id="projectInfoPanel" style="display: none;">
                    <h2>
                        <span id="projectNameText">{{ $project->projectName }}</span>
                        @if($role === 'contractor')
                        <button class="edit-info-btn" id="editButton" onclick="toggleEditMode()">
                            <i class="fa fa-edit"></i> <span id="editButtonText">Edit</span>
                        </button>
                        @endif
                    </h2>

                    <div class="project-address">
                        <p><span id="addressText">{{ $project->projectAddress }}</span></p>
                    </div>

                    <hr style="margin: 10px 0;">

                    <p style="font-size: 14px; font-weight: bold; padding-bottom: 10px;">Project Info</p>
                    <table class="project-info-table">
                        <tr>
                            <td><strong>Start Date:</strong></td>
                            <td><i class="fa fa-calendar" style="color:#808080; padding-right: 6px;"></i>
                                <span id="startDateText">{{ \Carbon\Carbon::parse($project->startDate)->format('Y-m-d') }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>End Date:</strong></td>
                            <td><i class="fa fa-calendar" style="color:#808080; padding-right: 6px;"></i>
                                <span id="endDateText">{{ \Carbon\Carbon::parse($project->endDate)->format('Y-m-d') }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Duration:</strong></td>
                            <td><i class="fa fa-clock" style="color:#808080; padding-right: 6px;"></i><span id="durationText">-</span></td>
                        </tr>
                        <tr>
                            <td><strong>Contractor:</strong></td>
                            <td class="user-cell">
                                @if($project->contractorUser)
                                <span class="user-avatar" style="background-color: #ff5ce8;">
                                    {{ strtoupper(substr($project->contractorUser->userName, 0, 1)) }}
                                </span>
                                {{ $project->contractorUser->userName }}
                                @else
                                N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Homeowner:</strong></td>
                            <td class="user-cell">
                                @if($project->homeownerUser)
                                <span class="user-avatar" style="background-color: #ff6912;">
                                    {{ strtoupper(substr($project->homeownerUser->userName, 0, 1)) }}
                                </span>
                                {{ $project->homeownerUser->userName }}
                                @else
                                N/A
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="status-badge {{ strtolower($project->projectStatus) }}">
                                    {{ $project->projectStatus }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Description:</strong></td>
                            <td>
                                <span id="descriptionText">{{ $project->projectDesc }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
                @include('generalComponent.viewNav')

                <div class="documents">
                    <div class="title">
                        <h3>Documents</h3>
                    </div>
                    <hr>

                    <div class="upload-btn-container">
                        <button class="upload-btn" onclick="toggleFileInfoModal(true)">Upload Document</button>
                        <input type="file" id="fileInput" style="display: none;" />
                    </div>

                    <!-- Modal Overlay -->
                    <div id="modalOverlay" class="modal-overlay"></div>

                    <!-- File Info Modal -->
                    <div id="fileInfoModal" class="file-info-modal">
                        <div class="modal-content">
                            <h3 class="modalTitle">Document Info</h3>

                            <div class="input-group">
                                <label for="fileInputModal">Select File:</label>
                                <input type="file" id="fileInputModal" onchange="handleFileSelect(event)" />
                            </div>

                            <div class="input-group">
                                <label>File Name:</label>
                                <span id="fileName" class="file-info"></span>
                            </div>

                            <div class="input-group">
                                <label>File Type:</label>
                                <span id="fileType" class="file-info"></span>
                            </div>

                            <div class="input-group">
                                <label for="description">Description (Optional):</label>
                                <textarea id="description" placeholder="Enter description (optional)"></textarea>
                            </div>

                            <!-- Message for success or error -->
                            <div id="uploadMessage" class="upload-message" style="font-size: 14px;"></div>

                            <div class="modal-buttons">
                                <button class="buttonCtrl" onclick="uploadDocument()" style="padding: 10px 30px; font-size: 14px;">Upload</button>
                                <button class="buttonCtrl" onclick="closeModal()" style="padding: 10px 30px; font-size: 14px;">Cancel</button>
                            </div>
                        </div>
                    </div>


                    <div class="search-bar">
                        <input type="text" placeholder="Search documents..." class="search-input">
                        <div class="filter-icon" onclick="filterAlphabetically()">
                            <img src="{{ asset('icon/filter.png') }}" alt="Filter" class="filter-icon-img">
                        </div>
                        <!-- Delete Button -->
                        <button id="deleteDocumentButton" class="delete-btn" disabled onclick="deleteSelectedDocuments()">
                            Delete Document
                        </button>
                    </div>

                    <div id="fileCount" class="file-count">
                        Showing <span id="foundFiles">0</span> out of <span id="totalFiles">0</span> files
                    </div>

                    <!-- Display Documents -->
                    @if(count($documents) > 0)
                    <div class="gallery">
                        @foreach($documents as $document)
                        <div class="file-item"
                             onclick="openDocumentViewer('{{ addslashes($document->documentID) }}', '{{ addslashes($document->fileType) }}', '{{ addslashes($document->documentName) }}', '{{ addslashes($document->description) }}', '{{ addslashes($document->created_at) }}')"
                             data-document-id="{{ $document->documentID }}">
                            <div class="file-checkbox">
                                <input type="checkbox" class="document-checkbox" data-document-id="{{ $document->documentID }}">
                            </div>
                            <div class="file-icon">
                                <img src="{{ getFileIcon($document->fileType) }}" alt="{{ $document->documentName }}">
                            </div>
                            <div class="file-info">
                                <span class="file-title">{{ $document->documentName }}</span>
                                <span class="file-description">{{ $document->description }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="no-documents">
                        <img src="{{ asset('images/folder.png') }}" alt="No Documents" class="empty-folder">
                        <p>No documents found. Upload one to get started.</p>
                    </div>
                    @endif


                    <!-- Document Viewer -->
                    <div id="documentViewer" class="document-viewer">
                        <div class="viewer-container">
                            <div class="viewer-content">
                                <iframe id="pdfViewer" class="hidden" style="width: 100%;"></iframe>
                                <div id="excelViewer" style="display:none;"></div>

                                <!-- Image viewer -->
                                <img id="imageViewer" class="hidden" />

                                <!-- Text viewer -->
                                <pre id="textViewer" class="hidden"></pre>
                            </div>

                            <div class="document-details">
                                <h3>Document Details</h3>
                                <table>
                                    <tr>
                                        <td><strong>Name:</strong></td>
                                        <td><span id="docName"></span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Type:</strong></td>
                                        <td>
                                            <div class="file-type-container">
                                                <img id="docTypeIcon" src="{{ asset('images/pdf-icon.png') }}" alt="File Type Icon" width="20px"/>
                                                <span id="docType"></span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Description:</strong></td>
                                        <td><span id="docDescription"></span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Uploaded On:</strong></td>
                                        <td><span id="docUploadTime"></span></td>
                                    </tr>
                                </table>

                                <!-- Single Download Button -->
                                <button id="downloadBtn" 
                                        data-document-id="" 
                                        onclick="downloadDocument()">Download</button>
                                <button class="close-btn" onclick="closeDocumentViewer()">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
        calculateDuration(); // Ensure this is called
        });
        let editMode = false;
        function toggleEditMode() {
        editMode = !editMode;
        const buttonText = document.getElementById("editButtonText");
        if (editMode) {
        buttonText.innerText = "Save"; // Edit -> Save
        } else {
        buttonText.innerText = "Edit"; // Save -> Edit
        saveProjectInfo();
        }

        const fields = [
        { id: "projectNameText", type: "text" },
        { id: "startDateText", type: "date" },
        { id: "endDateText", type: "date" },
        { id: "addressText", type: "text" },
        { id: "descriptionText", type: "text" }
        ];
        fields.forEach(field => {
        const existingElement = document.getElementById(field.id);
        const parent = existingElement.parentNode;
        if (editMode) {
        const input = document.createElement("input");
        input.type = field.type;
        input.value = existingElement.innerText.trim();
        input.id = field.id;
        input.className = "editable-field";
        parent.replaceChild(input, existingElement);
        } else {
        const input = document.getElementById(field.id);
        const span = document.createElement("span");
        span.id = field.id;
        span.innerText = input.value;
        parent.replaceChild(span, input);
        }
        });
        }




        function saveProjectInfo() {
        const getValue = (id) => {
        const element = document.getElementById(id);
        return element.tagName === 'INPUT' ? element.value : element.innerText.trim();
        };
        const data = {
        projectName: getValue("projectNameText"),
                startDate: getValue("startDateText"),
                endDate: getValue("endDateText"),
                projectAddress: getValue("addressText"),
                projectDesc: getValue("descriptionText"),
        };
        fetch(`/projects/update/{{ $project->projectID }}`, {
        method: "POST",
                headers: {
                "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(data)
        }).then(response => response.json())
                .then(result => {
                if (result.success) {
                location.reload();
                } else {
                alert("Failed to update project information.");
                }
                }).catch(error => {
        console.error("Error:", error);
        alert("An error occurred while updating project information.");
        });
        calculateDuration();
        }


        function toggleProjectInfo() {
        const panel = document.getElementById('projectInfoPanel');
        panel.style.display = (panel.style.display === 'none' || panel.style.display === '') ? 'block' : 'none';
        updateProjectTopStyle();
        }

// This keeps the project-top "active" state correct at all times
        function updateProjectTopStyle() {
        const panel = document.getElementById('projectInfoPanel');
        const projectTop = document.querySelector('.project-top');
        const icon = document.getElementById('projectInfoIcon');
        if (panel.style.display === 'block') {
        projectTop.classList.add('active');
        icon.classList.remove('fa-angle-down');
        icon.classList.add('fa-angle-up');
        } else {
        projectTop.classList.remove('active');
        icon.classList.remove('fa-angle-up');
        icon.classList.add('fa-angle-down');
        }
        }

// Call this immediately after any action that could hide/show the panel
        document.addEventListener('DOMContentLoaded', updateProjectTopStyle);
        function calculateDuration() {
        const startDateText = document.getElementById("startDateText").innerText.trim();
        const endDateText = document.getElementById("endDateText").innerText.trim();
        const durationText = document.getElementById("durationText");
        console.log("Start Date Text:", startDateText); // Debugging
        console.log("End Date Text:", endDateText); // Debugging

        const startDate = new Date(startDateText);
        const endDate = new Date(endDateText);
        if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
        console.error("Invalid Dates:", startDateText, endDateText); // Debugging
        durationText.innerText = "Invalid Dates";
        return;
        }

        const timeDiff = endDate - startDate;
        if (timeDiff < 0) {
        console.error("Invalid Date Range:", startDateText, endDateText); // Debugging
        durationText.innerText = "Invalid Date Range";
        return;
        }

        const days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24)) + 1;
        durationText.innerText = `${days} days`;
        }

        document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.querySelector(".search-input");
        const fileItems = document.querySelectorAll(".file-item");
        const noResultsMessage = document.getElementById("noResultsMessage");
        const foundFilesSpan = document.getElementById("foundFiles");
        const totalFilesSpan = document.getElementById("totalFiles");
        // Set the total files count initially
        totalFilesSpan.textContent = fileItems.length;
        // Search Function
        searchInput.addEventListener("input", function () {
        const searchText = this.value.toLowerCase();
        let foundFiles = 0;
        fileItems.forEach(file => {
        const title = file.querySelector(".file-title").textContent.toLowerCase();
        if (title.includes(searchText)) {
        file.style.display = ""; // Show matching file
        foundFiles++;
        } else {
        file.style.display = "none"; // Hide non-matching file
        }
        });
        // Update the found files count
        foundFilesSpan.textContent = foundFiles;
        // Display the "No matching documents found" message if no files match
        if (foundFiles === 0 && searchText.length > 0) {
        noResultsMessage.style.display = "block"; // Show "No results" message
        } else {
        noResultsMessage.style.display = "none"; // Hide the message when there are matches
        }
        });
        // Sorting Function (for alphabetical sorting)
        let ascendingOrder = true;
        const filterIcon = document.querySelector(".filter-icon-img");
        filterIcon.addEventListener("click", function () {
        const gallery = document.querySelector(".gallery");
        const fileItemsArray = Array.from(gallery.getElementsByClassName("file-item"));
        fileItemsArray.sort((a, b) => {
        const titleA = a.querySelector(".file-title").textContent.toLowerCase();
        const titleB = b.querySelector(".file-title").textContent.toLowerCase();
        return ascendingOrder ? titleA.localeCompare(titleB) : titleB.localeCompare(titleA);
        });
        // Toggle order for next click
        ascendingOrder = !ascendingOrder;
        // Append sorted items back to the gallery
        gallery.innerHTML = '';
        fileItemsArray.forEach(item => gallery.appendChild(item));
        });
        // Function to handle checkbox clicks
        function handleCheckboxClick(event) {
        event.stopPropagation(); // Prevent triggering the file item's click event

        const checkbox = event.target;
        const fileItem = checkbox.closest('.file-item'); // Get the parent file item

        if (checkbox.checked) {
        fileItem.classList.add('checked'); // Add red border and shadow
        } else {
        fileItem.classList.remove('checked'); // Remove red border and shadow
        }

        // Enable/disable the delete button based on checked checkboxes
        const checkedCheckboxes = document.querySelectorAll('.document-checkbox:checked');
        const deleteButton = document.getElementById('deleteDocumentButton');
        deleteButton.disabled = checkedCheckboxes.length === 0;
        }

        // Attach event listeners to checkboxes
        const checkboxes = document.querySelectorAll(".document-checkbox");
        checkboxes.forEach(checkbox => {
        checkbox.addEventListener("click", handleCheckboxClick);
        });
        // Function to delete selected documents
        window.deleteSelectedDocuments = function () {
        const checkedCheckboxes = document.querySelectorAll(".document-checkbox:checked");
        if (checkedCheckboxes.length === 0) {
        alert("No documents selected.");
        return;
        }

        // Confirm deletion
        if (!confirm("Are you sure you want to delete the selected documents?")) {
        return;
        }

        // Collect document IDs
        const documentIDs = Array.from(checkedCheckboxes).map(checkbox => checkbox.dataset.documentId);
        // Send delete request to the server
        fetch("{{ route('deleteDocuments') }}", {
        method: "POST",
                headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json",
                },
                body: JSON.stringify({ documentIDs }),
        })
                .then(response => response.json())
                .then(data => {
                if (data.success) {
                alert("Documents deleted successfully!");
                location.reload(); // Refresh the page to reflect changes
                } else {
                alert("Failed to delete documents.");
                }
                })
                .catch(error => {
                console.error("Error deleting documents:", error);
                alert("An error occurred while deleting documents.");
                });
        };
        });
        function handleFileSelect(event) {
        selectedFile = event.target.files[0];
        if (!selectedFile) {
        console.error("No valid file selected");
        return;
        }

        // Extract file extension (lowercase) from the file name
        const fileExtension = selectedFile.name.split('.').pop().toLowerCase();
        const fileTypeMap = {
        'pdf': 'PDF',
                'doc': 'DOC',
                'docx': 'DOCX',
                'xls': 'XLS',
                'xlsx': 'XLSX',
                'jpg': 'JPG',
                'jpeg': 'JPEG',
                'png': 'PNG',
                'txt': 'TXT',
                'zip': 'ZIP'
        };
        // Ensure the fileType is mapped correctly
        const readableFileType = fileTypeMap[fileExtension] || fileExtension.toUpperCase();
        // Update UI with file info
        document.getElementById('fileName').textContent = selectedFile.name;
        document.getElementById('fileType').textContent = readableFileType;
        // Store the updated file type (now reassignable)
        fileType = readableFileType.toLowerCase();
        // Ensure modal remains open after file is selected
        toggleFileInfoModal(true); // Pass `true` to keep the modal open
        }


// Function to toggle modal visibility
        function toggleFileInfoModal(openModal = false) {
        const modal = document.getElementById('fileInfoModal');
        const overlay = document.getElementById('modalOverlay');
        // Only toggle modal visibility if necessary
        if (openModal) {
        modal.style.display = 'block';
        overlay.style.display = 'block';
        } else {
        modal.style.display = 'none';
        overlay.style.display = 'none';
        }
        }

// Close modal
        function closeModal() {
        document.getElementById('fileInfoModal').style.display = 'none';
        document.getElementById('modalOverlay').style.display = 'none';
        }

        function uploadDocument() {
        const validTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'txt'];
        const description = document.getElementById('description').value;
        // Validate file type based on the file extension (lowercase)
        if (!validTypes.includes(fileType)) {
        displayMessage('Invalid file type. Please upload a valid document.', 'error');
        return;
        }

        const formData = new FormData();
        formData.append('file', selectedFile);
        formData.append('documentName', selectedFile.name);
        formData.append('description', description);
        formData.append('projectID', '{{ $project->projectID }}'); // Dynamically pass projectID

        fetch('{{ route('uploadDocument') }}', {
        method: 'POST',
                headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
        })
                .then(response => response.json())
                .then(data => {
                if (data.message) {
                // Display success message
                displayMessage(data.message, 'success');
                // Close the modal and reload the page
                closeModal();
                setTimeout(() => {
                location.reload();
                }, 2000); // Wait for 2 seconds before reloading
                }
                })
                .catch(error => {
                displayMessage('Error uploading document. Please try again.', 'error');
                });
        }


// Display success/error messages
        function displayMessage(message, type) {
        const messageElement = document.getElementById('uploadMessage');
        messageElement.textContent = message;
        messageElement.className = `upload-message ${type}`;
        }


        let filesSortedAlphabetically = false; // Track sorting state

        // Function to filter files alphabetically
        function filterAlphabetically() {
        var gallery = document.querySelector('.gallery');
        var fileItems = Array.from(gallery.getElementsByClassName('file-item'));
        fileItems.sort((a, b) => {
        var titleA = a.querySelector('.file-title').textContent.toUpperCase();
        var titleB = b.querySelector('.file-title').textContent.toUpperCase();
        return filesSortedAlphabetically ? titleB.localeCompare(titleA) : titleA.localeCompare(titleB);
        });
        gallery.innerHTML = '';
        fileItems.forEach(item => gallery.appendChild(item));
        filesSortedAlphabetically = !filesSortedAlphabetically;
        }
        function searchDocuments(event) {
        console.log("Search function triggered!"); // Debugging line
        var input = document.getElementById('searchInput').value.toUpperCase().trim();
        var gallery = document.querySelector('.gallery');
        if (!gallery) {
        console.error("Gallery element not found.");
        return;
        }

        var fileItems = gallery.getElementsByClassName('file-item');
        if (fileItems.length === 0) {
        console.log("No file items found.");
        return;
        }

        var totalFiles = fileItems.length;
        var foundFiles = 0;
        var noResultsMessage = document.getElementById('noResultsMessage');
        noResultsMessage.style.display = "none"; // Hide the no results message initially

        Array.from(fileItems).forEach(file => {
        var titleElement = file.querySelector('.file-title');
        if (!titleElement) {
        console.error("No file title found for item:", file);
        return;
        }

        var title = titleElement.textContent.toUpperCase().trim(); // Ensure the title is trimmed and in uppercase
        console.log("Searching for: ", input);
        console.log("File title: ", title);
        // Check if the title matches the search input
        if (title.includes(input)) {
        file.style.display = ""; // Show the file item
        foundFiles++;
        } else {
        file.style.display = "none"; // Hide the file item
        }
        });
        // Update the number of files found and total
        document.getElementById('foundFiles').textContent = foundFiles;
        document.getElementById('totalFiles').textContent = totalFiles;
        // Display a message when no files match the search criteria
        if (foundFiles === 0 && input.length > 0) {
        noResultsMessage.style.display = "block"; // Show "No results" message
        }
        }

        window.onload = function () {
        // Ensure search function is applied after the page content has loaded
        var gallery = document.querySelector('.gallery');
        var fileItems = gallery.getElementsByClassName('file-item');
        if (fileItems.length === 0) {
        console.log("No file items found on page load.");
        }
        document.getElementById('totalFiles').textContent = fileItems.length;
        // Call searchDocuments to initialize the view with all files visible
        searchDocuments();
        };
        function openDocumentViewer(documentID, fileType, name, description, uploadTime) {
        if (!documentID) {
        console.error("Error: Document ID is undefined!");
        return;
        }

        console.log("Opening document with ID:", documentID);
        closeDocumentViewer();
        // Update document details
        document.getElementById("documentViewer").style.display = "block";
        document.getElementById("modalOverlay").style.display = "block";
        document.getElementById("docName").textContent = name;
        document.getElementById("docType").textContent = fileType;
        document.getElementById("docDescription").textContent = description || "-";
        document.getElementById("docUploadTime").textContent = uploadTime;
        document.getElementById("docTypeIcon").src = getFileIcon(fileType);
        // Update the download button
        const downloadBtn = document.getElementById("downloadBtn");
        downloadBtn.setAttribute("data-document-id", documentID);
        downloadBtn.setAttribute("onclick", `downloadDocument('${documentID}')`);
        // Fetch and display document content
        fetch(`/getDocumentContent/${documentID}`)
                .then(response => response.json())
                .then(data => displayDocument(data.fileContent, fileType))
                .catch(error => console.error("Error loading document:", error));
        }


        function displayDocument(fileContent, fileType) {
        document.getElementById("pdfViewer").style.display = "none";
        document.getElementById("imageViewer").style.display = "none";
        document.getElementById("textViewer").style.display = "none";
        document.getElementById("excelViewer").style.display = "none"; // Ensure Excel viewer is hidden by default

        if (fileType === "pdf") {
        const pdfViewer = document.getElementById("pdfViewer");
        pdfViewer.style.display = "block";
        // Ensure fileContent is not undefined or empty
        if (fileContent) {
        const fileName = fileContent.split('/').pop(); // Extract file name from the path
        pdfViewer.src = `http://localhost/renovationsystem/public/storage/documents/${fileName}`;
        } else {
        console.error('Error: fileContent is undefined or empty');
        }
        } else if (["jpg", "jpeg", "png"].includes(fileType)) {
        document.getElementById("imageViewer").style.display = "block";
        document.getElementById("imageViewer").src = fileContent;
        } else if (fileType === "txt") {
        document.getElementById("textViewer").style.display = "block";
        fetch(fileContent)
                .then(response => response.text())
                .then(text => document.getElementById("textViewer").textContent = text)
                .catch(console.error);
        } else if (["doc", "docx"].includes(fileType)) {
        fetch(fileContent)
                .then(response => response.arrayBuffer())
                .then(buffer => {
                mammoth.convertToHtml({arrayBuffer: buffer})
                        .then(result => {
                        document.getElementById("textViewer").style.display = "block";
                        document.getElementById("textViewer").innerHTML = result.value;
                        })
                        .catch(console.error);
                })
                .catch(console.error);
        } else if (["xls", "xlsx"].includes(fileType)) {
        // Display the Excel viewer
        document.getElementById("excelViewer").style.display = "block";
        fetch(fileContent)
                .then(response => response.arrayBuffer())
                .then(data => {
                const workbook = XLSX.read(data, {type: 'array'});
                const sheetNames = workbook.SheetNames;
                const sheet = workbook.Sheets[sheetNames[0]]; // Get the first sheet

                const htmlString = XLSX.utils.sheet_to_html(sheet); // Convert sheet to HTML
                document.getElementById("excelViewer").innerHTML = htmlString; // Display it in the excelViewer
                })
                .catch(console.error);
        } else {
        alert("This file type is not supported for preview.");
        }
        }



        function getFileIcon(fileType) {
        const fileIcons = {
        pdf: '{{ asset("images/pdf-icon.png") }}',
                doc: '{{ asset("images/doc-icon.png") }}',
                docx: '{{ asset("images/docx-icon.png") }}',
                xls: '{{ asset("images/xls-icon.png") }}',
                xlsx: '{{ asset("images/xlsx-icon.png") }}',
                jpg: '{{ asset("images/jpg-icon.png") }}',
                jpeg: '{{ asset("images/jpeg-icon.png") }}',
                png: '{{ asset("images/png-icon.png") }}',
                txt: '{{ asset("images/txt-icon.png") }}',
        };
        return fileIcons[fileType.toLowerCase()] || '{{ asset("images/default-icon.png") }}';
        }

        function closeDocumentViewer() {
        document.getElementById("documentViewer").style.display = "none";
        document.getElementById("modalOverlay").style.display = "none";
        document.getElementById("pdfViewer").style.display = "none";
        document.getElementById("imageViewer").style.display = "none";
        document.getElementById("textViewer").style.display = "none";
        document.getElementById("pdfViewer").innerHTML = '';
        document.getElementById("imageViewer").src = '';
        document.getElementById("textViewer").textContent = '';
        }

        function downloadDocument(documentID) {
        if (!documentID) {
        console.error("Document ID not found!");
        return;
        }
        // Trigger the download by redirecting the browser
        window.location.href = `/downloadDocument/${documentID}`;
        }

        function disableButtonsBasedOnProjectStatus() {
        // Get the project status from the DOM
        const projectStatus = "{{ $project->projectStatus }}"; // Assuming the project status is available in the Blade template

        // Check if the project status is "completed"
        if (projectStatus.toLowerCase() === "completed") {
        // Disable upload and delete buttons
        const uploadButton = document.querySelector('.upload-btn');
        const deleteButton = document.getElementById('deleteDocumentButton');
        if (uploadButton) {
        uploadButton.disabled = true;
        uploadButton.style.opacity = 0.6; // Optional: Change appearance
        uploadButton.style.cursor = 'not-allowed'; // Optional: Change cursor
        }

        if (deleteButton) {
        deleteButton.disabled = true;
        deleteButton.style.opacity = 0.6; // Optional: Change appearance
        deleteButton.style.cursor = 'not-allowed'; // Optional: Change cursor
        }

        // Allow download, search, and filter buttons to remain enabled
        const downloadButtons = document.querySelectorAll('#downloadBtn, .search-input, .filter-icon');
        downloadButtons.forEach(button => {
        button.disabled = false; // Ensure these buttons are enabled
        button.style.opacity = 1; // Reset appearance
        button.style.cursor = 'pointer'; // Reset cursor
        });
        }
        }

// Call the function when the page loads
        document.addEventListener('DOMContentLoaded', disableButtonsBasedOnProjectStatus);




    </script>

</body>
