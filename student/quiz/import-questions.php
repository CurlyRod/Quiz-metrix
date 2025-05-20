<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Questions - Quiz Creator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
   

    <div class="container mt-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="mb-0">Import Questions</h2>
                    <div>
                        <button class="btn btn-outline-secondary me-2" id="cancelBtn">Cancel</button>
                        <button class="btn btn-primary" id="importBtn" style="background-color: #6366f1; color: white;">Import</button>
                    </div>
                </div>

                <div class="alert alert-danger d-none" id="errorAlert"></div>
                <div class="alert alert-success d-none" id="successAlert"></div>

                <div class="mb-4">
                    <h5>Import your data</h5>
                    <p class="text-muted small">Copy and Paste your data here</p>
                    <textarea class="form-control" id="importData" rows="12" placeholder="Term - Definition;&#10;Term - Definition;&#10;Term - Definition"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6>Between Term and Definition</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="termSeparator" id="dashSeparator" value="-" checked>
                                <label class="form-check-label" for="dashSeparator">
                                    Dash (-)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <h6>Between Cards</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cardSeparator" id="semicolonCardSeparator" value=";" checked>
                                <label class="form-check-label" for="semicolonCardSeparator">
                                    Semicolon (;)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/import-questions.js"></script>
</body>
</html>
