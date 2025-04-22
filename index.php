<!doctype html>
<html lang="en" class="h-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <title>Get PDF Information</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.css" rel="stylesheet">
    <script src="js/jquery.3.js"></script>

    <!-- PDF.js CDN -->
    <script src="//mozilla.github.io/pdf.js/build/pdf.mjs" type="module"></script>

    <!-- Custom styles for this template -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="mainContainer d-flex flex-column h-100" spellcheck="false">
<!-- Begin page content -->
<main role="main" class="flex-shrink-0 align-middle">
    <div class="container">
		
			<div class="row">

				<div class="col-sm-6 left-sidebar">
					<textarea class="btn-copy" id="clickme">CLICK HERE FOR FOOTNOTE CODE</textarea>
					<button class="buttonwide" onclick="copyFootnote(this)">Copy footnote</button>

					<textarea class="btn-copy" id="en-tag"><span lang="en" xml:lang="en"></textarea>
					<button class="buttonwide" onclick="copyEnglishLang(this)">Copy English tag</button>

					<textarea class="btn-copy" id="fr-tag"><span lang="fr" xml:lang="fr"></textarea>
					<button class="buttonwide" onclick="copyFrenchLang(this)">Copy French tag</button>

					<!-- Title Case Tool -->
					<textarea class="form-control mt-4" id="titlecase-input" rows="3" placeholder="Paste text here to convert..."></textarea>
					<div class="button-group d-flex justify-content-between mt-1">
						<button class="button-half mr-1" id="titlecase-button">Title Case</button>
						<button class="button-half ml-1" id="lowercase-button">lowercase</button>
					</div>
				</div>


				<div class="col-sm-6 right-sidebar">
					<div class="card" style="margin-top: 33px;">
						<div class="card-body">
							<form method="post">
								<fieldset class="p-3" style="border: 1px solid black;">
									<legend class="w-auto">PDF URL</legend>
									<div class="text-center">
										<input type="url" class="form-control" name="pdf_link" id="pdf_link"/>
										<!-- Progress Bar -->
										<div id="pdf-progress-container" class="progress mt-2" style="height: 20px; display: none;">
											<div id="pdf-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
										</div>
										<button id="submit" class="btn btn-outline-dark mt-3 d-inline-block">Get file size and
											number of pages
										</button>
									</div>
								</fieldset>
								<fieldset class="p-3 mt-5 bg-light" style="border: 1px solid black;">
									<legend class="w-auto">English</legend>										<div class="" id="output_en-info">                        </div>						
									<div class="" id="output_en" style="position: relative;">
										<span class="copy-output-button" title="Copy content">&#x1F4CB;</span> <!-- Clipboard icon -->
									</div>
								</fieldset>


								<fieldset class="p-3 mt-5 bg-light" style="border: 1px solid black;">
									<legend class="w-auto">Fran&ccedil;ais</legend>										<div class="" id="output_fr-info">                        </div>						
									<div class="" id="output_fr" style="position: relative;">
										<span class="copy-output-button" title="Copy content">&#x1F4CB;</span> <!-- Clipboard icon -->
									</div>
								</fieldset>
							</form>
						</div>
					</div>
				</div>
			</div>

    </div>
</main>
<!-- script for the top copy part -->
<script>
	// for increase number when clicking textarea
	var textarea = document.getElementById("clickme"),
	  count = 0;
	textarea.onclick = function() {
	  count += 1;
	// mili removed the name tag ->  textarea.innerHTML = "&lt;a id=&quot;txt" + count + "&quot;&gt;&lt;/a&gt;&lt;a class=&quot;footnote&quot; href=&quot;#ftn" + count + "&quot; title=&quot;Note " + count + "&quot;&gt;" + count + "&lt;/a&gt;";
	  textarea.innerHTML = "&lt;a id=&quot;txt" + count + "&quot;&gt;&lt;/a&gt;&lt;a class=&quot;footnote&quot; href=&quot;#ftn" + count + "&quot; title=&quot;Note " + count + "&quot;&gt;" + count + "&lt;/a&gt;";
	};

	// for copy button of footnote: 
	function copyFootnote(button) {
		var copyTxt = document.getElementById("clickme");
		copyTxt.select();
		document.execCommand("copy");
		// Add feedback
		const originalText = button.innerText;
		button.innerText = "Copied!";
		button.disabled = true;
		setTimeout(() => {
			button.innerText = originalText;
			button.disabled = false;
		}, 1500);
	};

	// for copy English lang tag: 
	function copyEnglishLang(button) {
		var copyTxt = document.getElementById("en-tag");
		copyTxt.select();
		document.execCommand("copy");
		// Add feedback
		const originalText = button.innerText;
		button.innerText = "Copied!";
		button.disabled = true;
		setTimeout(() => {
			button.innerText = originalText;
			button.disabled = false;
		}, 1500);
	};

	// for copy French lang tag: 
	function copyFrenchLang(button) {
		var copyTxt = document.getElementById("fr-tag");
		copyTxt.select();
		document.execCommand("copy");
		// Add feedback
		const originalText = button.innerText;
		button.innerText = "Copied!";
		button.disabled = true;
		setTimeout(() => {
			button.innerText = originalText;
			button.disabled = false;
		}, 1500);
	};

	// --- Title Case Conversion Function --- 
    // Moved here to be in the same module scope as the listener
	function convertToTitleCase(str) {
		const minorWords = new Set([
			'a', 'an', 'the', 'and', 'but', 'or', 'for', 'nor', 'on', 'at', 'to', 'from', 'by', 'in', 'of', 'off', 'out', 'over', 'up', 'with', 'as'
		]);
		if (!str) return '';

		let words = str.split(' '); // Split by space
		let resultWords = [];

		for (let i = 0; i < words.length; i++) {
			let word = words[i];
			if (!word) continue; // Skip empty strings from multiple spaces

			// Preserve case if:
			// 1. It contains a hyphen
			// 2. It contains BOTH uppercase and lowercase letters (mixed case)
			let preserveCase = word.includes('-') 
							   || (word.match(/[a-z]/) && word.match(/[A-Z]/));

			// Extract punctuation for accurate minor word check
			let coreWord = word.replace(/^[\W_]+|[\W_]+$/g, '').toLowerCase(); // Use \W_ for non-word chars including underscore
			let prefix = word.match(/^[\W_]+/)?.[0] || '';
			let suffix = word.match(/[\W_]+$/)?.[0] || '';
			let wordContent = word.substring(prefix.length, word.length - suffix.length);
			let isMinor = minorWords.has(coreWord);
			let endsInPeriod = suffix === '.';

			if (preserveCase) {
				resultWords.push(word); // Keep original case completely
			} else if (i === 0 || !isMinor || endsInPeriod || coreWord === '') {
				if (wordContent) {
					 resultWords.push(prefix + wordContent.charAt(0).toUpperCase() + wordContent.slice(1).toLowerCase() + suffix);
				} else {
					 resultWords.push(prefix + suffix); // Only punctuation, keep original
				}
			} else { // Minor word (and doesn't end in a period)
				resultWords.push(word.toLowerCase());
			}
		}
		return resultWords.join(' ');
	}

    $(document).ready(function () {
        var loader_html = $("#btn_loader").html(); // Get loader HTML once
        const $progressContainer = $('#pdf-progress-container');
        const $progressBar = $('#pdf-progress-bar');

        // --- Progress Update Function --- 
        function updateProgress(progressData) {
            if (progressData.total > 0) {
                const percent = Math.round((progressData.loaded / progressData.total) * 100);
                $progressBar.css('width', percent + '%').attr('aria-valuenow', percent).text(percent + '%');
            } else {
                // Fallback if total size is unknown
                const loadedMB = (progressData.loaded / 1024 / 1024).toFixed(2);
                $progressBar.css('width', '100%').text(`Loading ${loadedMB} MB...`); // Indeterminate look
            }
        }

        // --- Attach Title Case Listener --- 
        // Moved here to ensure the button exists in the DOM
        const titleCaseButton = document.getElementById('titlecase-button');
        const titleCaseInput = document.getElementById('titlecase-input');
        if (titleCaseButton && titleCaseInput) { 
            titleCaseButton.addEventListener('click', function() {
                 console.log("Title Case button clicked."); // DEBUG
                 console.log("Input before: ", titleCaseInput.value); // DEBUG
                 // Now the function is defined in the same scope
                 titleCaseInput.value = convertToTitleCase(titleCaseInput.value);
                 console.log("Input after: ", titleCaseInput.value); // DEBUG
            });
        } else {
            console.error('Could not find Title Case button or input area.');
        }

        // --- Attach Lowercase Listener --- 
        const lowercaseButton = document.getElementById('lowercase-button');
        if (lowercaseButton && titleCaseInput) { // Reuse titleCaseInput reference
            lowercaseButton.addEventListener('click', function() {
                console.log("Lowercase button clicked."); // DEBUG
                console.log("Input before: ", titleCaseInput.value); // DEBUG
                titleCaseInput.value = titleCaseInput.value.toLowerCase();
                console.log("Input after: ", titleCaseInput.value); // DEBUG
            });
        } else {
            console.error('Could not find Lowercase button or input area.');
        }

        // --- Attach Copy Button Listener (Delegated) ---
        $('body').on('click', '.copy-output-button', function(event) {
            const button = $(this);
            const outputDiv = button.parent(); // Get the parent (#output_en or #output_fr)
            const textToCopy = outputDiv.attr('data-copy-text'); 

            if (textToCopy && navigator.clipboard) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    // Success!
                    console.log('Copied to clipboard:', textToCopy);
                    const originalContent = button.html();
                    button.html('&#x2705;'); // Checkmark icon
                    button.addClass('copied');

                    // Reset after a delay
                    setTimeout(() => {
                        button.html(originalContent);
                        button.removeClass('copied');
                    }, 1500); // Reset after 1.5 seconds

                }).catch(err => {
                    console.error('Failed to copy text: ', err);
                    alert('Failed to copy text. See console for details.');
                });
            } else if (!navigator.clipboard) {
                 alert('Clipboard API not available in this browser.');
            } else {
                console.error('Could not find text to copy from data-copy-text attribute.');
            }
        });

        // --- Attach Special Char Copy Listener (Delegated) ---
        $('body').on('click', '.copy-special-char', function(event) {
            const button = $(this);
            // Get the raw attribute value to avoid entity decoding
            const codeToCopy = button.get(0).getAttribute('data-copy-code'); 
            const originalText = button.text(); // Store original text

            if (codeToCopy && navigator.clipboard) {
                navigator.clipboard.writeText(codeToCopy).then(() => {
                    // Success!
                    console.log('Copied special character code:', codeToCopy);
                    button.text('Copied!'); // Simple text feedback
                    button.prop('disabled', true); // Briefly disable

                    // Reset after a delay
                    setTimeout(() => {
                        button.text(originalText);
                        button.prop('disabled', false);
                    }, 1500); // Reset after 1.5 seconds

                }).catch(err => {
                    console.error('Failed to copy special char code: ', err);
                    alert('Failed to copy text. See console for details.');
                });
            } else if (!navigator.clipboard) {
                 alert('Clipboard API not available in this browser.');
            } else {
                console.error('Could not find code to copy from data-copy-code attribute.');
            }
        });

        $("#submit").click(function (e) {
            e.preventDefault();
            const pdfUrl = $("#pdf_link").val();

            if (!pdfUrl) {
                alert("Please enter a PDF URL.");
                return;
            }

            // Show loaders & Reset/Show INDETERMINATE Progress Bar
            $("#output_en-info").html(loader_html).fadeIn();
            $("#output_fr-info").html(loader_html).fadeIn(); 
            $("#output_en").empty();
            $("#output_fr").empty();
            $progressBar
                .css('width', '100%') // Full width for indeterminate
                .addClass('progress-bar-striped progress-bar-animated') // Ensure animated classes are present
                .attr('aria-valuenow', 0) // Still 0 for value
                .text('Downloading PDF...'); // Indicate download phase
            $progressContainer.show();

            // --- Get File Size via AJAX --- 
            const fileSizePromise = $.post('scripts/get_info.php', { url: pdfUrl }, null, "json")
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error fetching file size:", textStatus, errorThrown);
                     // Return a default/error object for size
                    return { file_size: {en: "N/A", fr: "N/D"}, error: "Server error fetching size." }; 
                });

            // --- Get Page Count via PDF.js --- 
            // Construct the proxy URL
            const proxyUrl = `scripts/proxy_pdf.php?url=${encodeURIComponent(pdfUrl)}`;
            
            // Pass url and onProgress callback to getDocument
            const pageCountPromise = pdfjsLib.getDocument({ 
                url: proxyUrl, 
                onProgress: (progressData) => { // Use arrow function for simplicity
                    // --- Switch to Determinate Progress --- 
                    // Remove indeterminate style only when actual progress starts
                    $progressBar.removeClass('progress-bar-striped progress-bar-animated');
                    updateProgress(progressData); // Call original update function
                }
            }).promise 
                .then(pdfDoc => {
                    return pdfDoc.numPages; // Success: return page count
                })
                .catch(error => {
                    console.error("PDF.js Error:", error);
                    // Check for specific error types if needed
                    let errorMessage = "Could not load PDF or get page count.";
                    if (error.name === 'MissingPDFException') {
                         errorMessage = `File not found or invalid URL: ${error.message}`;
                    } else if (error.name === 'UnexpectedResponseException') {
                         errorMessage = `Invalid response from server: ${error.message}`;
                    } else if (error.message) {
                         errorMessage = error.message; // Use generic message if available
                    } 
                    // Return an error indicator for page count
                    return { error: errorMessage }; 
                });

            // --- Process results when both promises complete --- 
            Promise.all([fileSizePromise, pageCountPromise])
                .then(([sizeData, pageData]) => {
                    
                    let numPages = 0;
                    let pageCountError = null;
                    let fileSizeError = sizeData.error || null;
                    let fileSize_en = "N/A";
                    let fileSize_fr = "N/D";

                    // Process page count result
                    if (typeof pageData === 'number') {
                        numPages = pageData;
                    } else if (pageData && pageData.error) {
                        pageCountError = pageData.error;
                    }

                    // Process file size result
                    if (!fileSizeError && sizeData.file_size) {
                        fileSize_en = sizeData.file_size.en;
                        fileSize_fr = sizeData.file_size.fr;
                    } else {
                         // Use the error from the AJAX call if it exists
                         fileSizeError = fileSizeError || "Could not retrieve file size.";
                    }

                    // --- Update UI --- 
                    let pages_str_en = numPages === 1 ? "page" : "pages";
                    let pages_str_fr = numPages === 1 ? "page" : "pages"; // Assuming same pluralization for 'page'

                    // Clear loaders and errors
                    $("#output_en-info").empty();
                    $("#output_fr-info").empty();

                    if (pageCountError) {
                        $("#output_en-info").html(`<span style="color: red; font-weight: bold;">Page Count Error:</span> ${pageCountError}`);
                        $("#output_fr-info").html(`<span style="color: red; font-weight: bold;">Erreur (Nombre de pages):</span> ${pageCountError}`); // Simple translation placeholder
                    }
                    if (fileSizeError) {
                         // Append file size error if it exists
                         const errorHtmlEn = `<br/><span style="color: red; font-weight: bold;">File Size Error:</span> ${fileSizeError}`;
                         const errorHtmlFr = `<br/><span style="color: red; font-weight: bold;">Erreur (Taille du fichier):</span> ${fileSizeError}`;
                         $("#output_en-info").append(errorHtmlEn);
                         $("#output_fr-info").append(errorHtmlFr);
                    }

                    // Display success info only if no errors occurred for both
                    if (!pageCountError && !fileSizeError && numPages > 0) {
                         $("#output_en-info").html(`This PDF has <strong>${numPages}</strong> ${pages_str_en} and the file is <strong>${fileSize_en}</strong>`);
                         $("#output_fr-info").html(`Ce PDF a <strong>${numPages}</strong> ${pages_str_fr} et le fichier est <strong>${fileSize_fr}</strong>`);
                         
                         // Construct the raw string to be displayed as text
                         const rawImgTag = '&nbsp;<img src="/staticfiles/PublicWebsite/assets/images/Common/pdficon_small.gif" alt="pdf"/>&nbsp;';
                         // Use &nbsp; before units (handled in PHP) and before pages
                         const rawInfoEn = `(${fileSize_en},&nbsp;${numPages}&nbsp;${pages_str_en})`; 
                         const rawInfoFr = `(${fileSize_fr},&nbsp;${numPages}&nbsp;${pages_str_fr})`;
                         let displayStringEn = rawImgTag + rawInfoEn;
                         let displayStringFr = rawImgTag + rawInfoFr;

                         // Escape &, < and > for display as text
                         // Order matters: Escape & first!
                         displayStringEn = displayStringEn.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                         displayStringFr = displayStringFr.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

                         // Use .html() with the fully escaped string for display
                         // Store the full HTML string in data-copy-text
                         $("#output_en").attr('data-copy-text', rawImgTag + rawInfoEn).html(displayStringEn + '<span class="copy-output-button" title="Copy content">&#x1F4CB;</span>');
                         $("#output_fr").attr('data-copy-text', rawImgTag + rawInfoFr).html(displayStringFr + '<span class="copy-output-button" title="Copy content">&#x1F4CB;</span>');
                         
                    } else if (!pageCountError && !fileSizeError && numPages === 0) {
                        // Handle case where PDF technically loads but has 0 pages
                         $("#output_en-info").html(`Successfully loaded PDF, but it reports <strong>0 pages</strong>. File size: <strong>${fileSize_en}</strong>`);
                         $("#output_fr-info").html(`PDF chargé avec succès, mais il signale <strong>0 pages</strong>. Taille du fichier: <strong>${fileSize_fr}</strong>`);
                    }

                    // --- Hide Progress Bar --- 
                    $progressContainer.hide();
                })
                .catch(error => {
                     // Catch any unexpected errors from Promise.all or .then()
                     // --- Hide Progress Bar on Error --- 
                     $progressContainer.hide();
                     console.error("General Error processing PDF info:", error);
                     $("#output_en-info").html(`<span style="color: red; font-weight: bold;">Unexpected Error:</span> Could not process PDF information.`);
                     $("#output_fr-info").html(`<span style="color: red; font-weight: bold;">Erreur inattendue:</span> Impossible de traiter les informations PDF.`);
                     $("#output_en").empty();
                     $("#output_fr").empty();
                });
        });		
    });
</script>
		
<!-- script for lower PDF fetch part -->
<script type="module"> 
    // Import PDF.js library using ES module syntax
    import * as pdfjsLib from '//mozilla.github.io/pdf.js/build/pdf.mjs';
    
    // Specify the worker source immediately after import
    // If the import fails, an error would likely occur before this line anyway.
    pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.mjs';
    /* Removed check:
    // Ensure the pdfjsLib variable is defined before accessing its properties
    if (pdfjsLib) {
      pdfjsLib.GlobalWorkerOptions.workerSrc = '//mozilla.github.io/pdf.js/build/pdf.worker.mjs';
    } else {
      console.error("PDF.js library failed to load.");
      // Optionally display an error to the user here
    }
    */

	// --- Title Case Conversion Function --- 
    // Moved here to be in the same module scope as the listener
	function convertToTitleCase(str) {
		const minorWords = new Set([
			'a', 'an', 'the', 'and', 'but', 'or', 'for', 'nor', 'on', 'at', 'to', 'from', 'by', 'in', 'of', 'off', 'out', 'over', 'up', 'with', 'as'
		]);
		if (!str) return '';

		let words = str.split(' '); // Split by space
		let resultWords = [];

		for (let i = 0; i < words.length; i++) {
			let word = words[i];
			if (!word) continue; // Skip empty strings from multiple spaces

			// Preserve case if:
			// 1. It contains a hyphen
			// 2. It contains BOTH uppercase and lowercase letters (mixed case)
			let preserveCase = word.includes('-') 
							   || (word.match(/[a-z]/) && word.match(/[A-Z]/));

			// Extract punctuation for accurate minor word check
			let coreWord = word.replace(/^[\W_]+|[\W_]+$/g, '').toLowerCase(); // Use \W_ for non-word chars including underscore
			let prefix = word.match(/^[\W_]+/)?.[0] || '';
			let suffix = word.match(/[\W_]+$/)?.[0] || '';
			let wordContent = word.substring(prefix.length, word.length - suffix.length);
			let isMinor = minorWords.has(coreWord);
			let endsInPeriod = suffix === '.';

			if (preserveCase) {
				resultWords.push(word); // Keep original case completely
			} else if (i === 0 || !isMinor || endsInPeriod || coreWord === '') {
				if (wordContent) {
					 resultWords.push(prefix + wordContent.charAt(0).toUpperCase() + wordContent.slice(1).toLowerCase() + suffix);
				} else {
					 resultWords.push(prefix + suffix); // Only punctuation, keep original
				}
			} else { // Minor word (and doesn't end in a period)
				resultWords.push(word.toLowerCase());
			}
		}
		return resultWords.join(' ');
	}

    $(document).ready(function () {
        var loader_html = $("#btn_loader").html(); // Get loader HTML once
        const $progressContainer = $('#pdf-progress-container');
        const $progressBar = $('#pdf-progress-bar');

        // --- Progress Update Function --- 
        function updateProgress(progressData) {
            if (progressData.total > 0) {
                const percent = Math.round((progressData.loaded / progressData.total) * 100);
                $progressBar.css('width', percent + '%').attr('aria-valuenow', percent).text(percent + '%');
            } else {
                // Fallback if total size is unknown
                const loadedMB = (progressData.loaded / 1024 / 1024).toFixed(2);
                $progressBar.css('width', '100%').text(`Loading ${loadedMB} MB...`); // Indeterminate look
            }
        }

        // --- Attach Title Case Listener --- 
        // Moved here to ensure the button exists in the DOM
        const titleCaseButton = document.getElementById('titlecase-button');
        const titleCaseInput = document.getElementById('titlecase-input');
        if (titleCaseButton && titleCaseInput) { 
            titleCaseButton.addEventListener('click', function() {
                 console.log("Title Case button clicked."); // DEBUG
                 console.log("Input before: ", titleCaseInput.value); // DEBUG
                 // Now the function is defined in the same scope
                 titleCaseInput.value = convertToTitleCase(titleCaseInput.value);
                 console.log("Input after: ", titleCaseInput.value); // DEBUG
            });
        } else {
            console.error('Could not find Title Case button or input area.');
        }

        // --- Attach Lowercase Listener --- 
        const lowercaseButton = document.getElementById('lowercase-button');
        if (lowercaseButton && titleCaseInput) { // Reuse titleCaseInput reference
            lowercaseButton.addEventListener('click', function() {
                console.log("Lowercase button clicked."); // DEBUG
                console.log("Input before: ", titleCaseInput.value); // DEBUG
                titleCaseInput.value = titleCaseInput.value.toLowerCase();
                console.log("Input after: ", titleCaseInput.value); // DEBUG
            });
        } else {
            console.error('Could not find Lowercase button or input area.');
        }

        // --- Attach Copy Button Listener (Delegated) ---
        $('body').on('click', '.copy-output-button', function(event) {
            const button = $(this);
            const outputDiv = button.parent(); // Get the parent (#output_en or #output_fr)
            const textToCopy = outputDiv.attr('data-copy-text'); 

            if (textToCopy && navigator.clipboard) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    // Success!
                    console.log('Copied to clipboard:', textToCopy);
                    const originalContent = button.html();
                    button.html('&#x2705;'); // Checkmark icon
                    button.addClass('copied');

                    // Reset after a delay
                    setTimeout(() => {
                        button.html(originalContent);
                        button.removeClass('copied');
                    }, 1500); // Reset after 1.5 seconds

                }).catch(err => {
                    console.error('Failed to copy text: ', err);
                    alert('Failed to copy text. See console for details.');
                });
            } else if (!navigator.clipboard) {
                 alert('Clipboard API not available in this browser.');
            } else {
                console.error('Could not find text to copy from data-copy-text attribute.');
            }
        });

        // --- Attach Special Char Copy Listener (Delegated) ---
        $('body').on('click', '.copy-special-char', function(event) {
            const button = $(this);
            // Get the raw attribute value to avoid entity decoding
            const codeToCopy = button.get(0).getAttribute('data-copy-code'); 
            const originalText = button.text(); // Store original text

            if (codeToCopy && navigator.clipboard) {
                navigator.clipboard.writeText(codeToCopy).then(() => {
                    // Success!
                    console.log('Copied special character code:', codeToCopy);
                    button.text('Copied!'); // Simple text feedback
                    button.prop('disabled', true); // Briefly disable

                    // Reset after a delay
                    setTimeout(() => {
                        button.text(originalText);
                        button.prop('disabled', false);
                    }, 1500); // Reset after 1.5 seconds

                }).catch(err => {
                    console.error('Failed to copy special char code: ', err);
                    alert('Failed to copy text. See console for details.');
                });
            } else if (!navigator.clipboard) {
                 alert('Clipboard API not available in this browser.');
            } else {
                console.error('Could not find code to copy from data-copy-code attribute.');
            }
        });

        $("#submit").click(function (e) {
            e.preventDefault();
            const pdfUrl = $("#pdf_link").val();

            if (!pdfUrl) {
                alert("Please enter a PDF URL.");
                return;
            }

            // Show loaders & Reset/Show INDETERMINATE Progress Bar
            $("#output_en-info").html(loader_html).fadeIn();
            $("#output_fr-info").html(loader_html).fadeIn(); 
            $("#output_en").empty();
            $("#output_fr").empty();
            $progressBar
                .css('width', '100%') // Full width for indeterminate
                .addClass('progress-bar-striped progress-bar-animated') // Ensure animated classes are present
                .attr('aria-valuenow', 0) // Still 0 for value
                .text('Downloading PDF...'); // Indicate download phase
            $progressContainer.show();

            // --- Get File Size via AJAX --- 
            const fileSizePromise = $.post('scripts/get_info.php', { url: pdfUrl }, null, "json")
                .fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("AJAX Error fetching file size:", textStatus, errorThrown);
                     // Return a default/error object for size
                    return { file_size: {en: "N/A", fr: "N/D"}, error: "Server error fetching size." }; 
                });

            // --- Get Page Count via PDF.js --- 
            // Construct the proxy URL
            const proxyUrl = `scripts/proxy_pdf.php?url=${encodeURIComponent(pdfUrl)}`;
            
            // Pass url and onProgress callback to getDocument
            const pageCountPromise = pdfjsLib.getDocument({ 
                url: proxyUrl, 
                onProgress: (progressData) => { // Use arrow function for simplicity
                    // --- Switch to Determinate Progress --- 
                    // Remove indeterminate style only when actual progress starts
                    $progressBar.removeClass('progress-bar-striped progress-bar-animated');
                    updateProgress(progressData); // Call original update function
                }
            }).promise 
                .then(pdfDoc => {
                    return pdfDoc.numPages; // Success: return page count
                })
                .catch(error => {
                    console.error("PDF.js Error:", error);
                    // Check for specific error types if needed
                    let errorMessage = "Could not load PDF or get page count.";
                    if (error.name === 'MissingPDFException') {
                         errorMessage = `File not found or invalid URL: ${error.message}`;
                    } else if (error.name === 'UnexpectedResponseException') {
                         errorMessage = `Invalid response from server: ${error.message}`;
                    } else if (error.message) {
                         errorMessage = error.message; // Use generic message if available
                    } 
                    // Return an error indicator for page count
                    return { error: errorMessage }; 
                });

            // --- Process results when both promises complete --- 
            Promise.all([fileSizePromise, pageCountPromise])
                .then(([sizeData, pageData]) => {
                    
                    let numPages = 0;
                    let pageCountError = null;
                    let fileSizeError = sizeData.error || null;
                    let fileSize_en = "N/A";
                    let fileSize_fr = "N/D";

                    // Process page count result
                    if (typeof pageData === 'number') {
                        numPages = pageData;
                    } else if (pageData && pageData.error) {
                        pageCountError = pageData.error;
                    }

                    // Process file size result
                    if (!fileSizeError && sizeData.file_size) {
                        fileSize_en = sizeData.file_size.en;
                        fileSize_fr = sizeData.file_size.fr;
                    } else {
                         // Use the error from the AJAX call if it exists
                         fileSizeError = fileSizeError || "Could not retrieve file size.";
                    }

                    // --- Update UI --- 
                    let pages_str_en = numPages === 1 ? "page" : "pages";
                    let pages_str_fr = numPages === 1 ? "page" : "pages"; // Assuming same pluralization for 'page'

                    // Clear loaders and errors
                    $("#output_en-info").empty();
                    $("#output_fr-info").empty();

                    if (pageCountError) {
                        $("#output_en-info").html(`<span style="color: red; font-weight: bold;">Page Count Error:</span> ${pageCountError}`);
                        $("#output_fr-info").html(`<span style="color: red; font-weight: bold;">Erreur (Nombre de pages):</span> ${pageCountError}`); // Simple translation placeholder
                    }
                    if (fileSizeError) {
                         // Append file size error if it exists
                         const errorHtmlEn = `<br/><span style="color: red; font-weight: bold;">File Size Error:</span> ${fileSizeError}`;
                         const errorHtmlFr = `<br/><span style="color: red; font-weight: bold;">Erreur (Taille du fichier):</span> ${fileSizeError}`;
                         $("#output_en-info").append(errorHtmlEn);
                         $("#output_fr-info").append(errorHtmlFr);
                    }

                    // Display success info only if no errors occurred for both
                    if (!pageCountError && !fileSizeError && numPages > 0) {
                         $("#output_en-info").html(`This PDF has <strong>${numPages}</strong> ${pages_str_en} and the file is <strong>${fileSize_en}</strong>`);
                         $("#output_fr-info").html(`Ce PDF a <strong>${numPages}</strong> ${pages_str_fr} et le fichier est <strong>${fileSize_fr}</strong>`);
                         
                         // Construct the raw string to be displayed as text
                         const rawImgTag = '&nbsp;<img src="/staticfiles/PublicWebsite/assets/images/Common/pdficon_small.gif" alt="pdf"/>&nbsp;';
                         // Use &nbsp; before units (handled in PHP) and before pages
                         const rawInfoEn = `(${fileSize_en},&nbsp;${numPages}&nbsp;${pages_str_en})`; 
                         const rawInfoFr = `(${fileSize_fr},&nbsp;${numPages}&nbsp;${pages_str_fr})`;
                         let displayStringEn = rawImgTag + rawInfoEn;
                         let displayStringFr = rawImgTag + rawInfoFr;

                         // Escape &, < and > for display as text
                         // Order matters: Escape & first!
                         displayStringEn = displayStringEn.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                         displayStringFr = displayStringFr.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

                         // Use .html() with the fully escaped string for display
                         // Store the full HTML string in data-copy-text
                         $("#output_en").attr('data-copy-text', rawImgTag + rawInfoEn).html(displayStringEn + '<span class="copy-output-button" title="Copy content">&#x1F4CB;</span>');
                         $("#output_fr").attr('data-copy-text', rawImgTag + rawInfoFr).html(displayStringFr + '<span class="copy-output-button" title="Copy content">&#x1F4CB;</span>');
                         
                    } else if (!pageCountError && !fileSizeError && numPages === 0) {
                        // Handle case where PDF technically loads but has 0 pages
                         $("#output_en-info").html(`Successfully loaded PDF, but it reports <strong>0 pages</strong>. File size: <strong>${fileSize_en}</strong>`);
                         $("#output_fr-info").html(`PDF chargé avec succès, mais il signale <strong>0 pages</strong>. Taille du fichier: <strong>${fileSize_fr}</strong>`);
                    }

                    // --- Hide Progress Bar --- 
                    $progressContainer.hide();
                })
                .catch(error => {
                     // Catch any unexpected errors from Promise.all or .then()
                     // --- Hide Progress Bar on Error --- 
                     $progressContainer.hide();
                     console.error("General Error processing PDF info:", error);
                     $("#output_en-info").html(`<span style="color: red; font-weight: bold;">Unexpected Error:</span> Could not process PDF information.`);
                     $("#output_fr-info").html(`<span style="color: red; font-weight: bold;">Erreur inattendue:</span> Impossible de traiter les informations PDF.`);
                     $("#output_en").empty();
                     $("#output_fr").empty();
                });
        });		
    });
</script>

<div id="btn_loader" class="text-center" style="display: none;">
    <img src="img/btn_loader.gif" class="img-responsive" style="height: 24px;">
</div>
<footer class="footer mt-auto py-3">
    <div class="container">
        <span class="text-muted"></span>
        <div class="special-chars-container mt-2 text-center">
            <small class="text-light d-block mb-1">Copy Special Characters:</small>
            <button class="btn btn-sm btn-outline-light mr-2 copy-special-char" data-copy-code="&amp;#8209;">non-breaking hyphen &#8209;</button>
            <button class="btn btn-sm btn-outline-light mr-2 copy-special-char" data-copy-code="&amp;#8212;">em dash &mdash;</button>
            <!-- Add more buttons here as needed -->
        </div>
    </div>
</footer>
</body>
</html>
