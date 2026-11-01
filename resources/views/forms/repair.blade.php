<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Repair and Maintenance Form – University of Antique</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Source+Sans+3:wght@400;600;700&display=swap');

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'Source Sans 3', sans-serif;
    font-size: 13px;
    background: #e8e4dc;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    padding: 32px 16px;
    color: #1a1a1a;
  }

  .page {
    background: #fff;
    width: 816px;
    padding: 48px 56px;
    box-shadow: 0 4px 32px rgba(0,0,0,.15);
  }
 
  .header {
    text-align: center;
    margin-bottom: 22px;
  }
  .header .republic {
    font-family: 'EB Garamond', serif;
    font-size: 12px;
    letter-spacing: .04em;
    color: #555;
    margin-bottom: 2px;
  }
  .header .university {
    font-family: 'EB Garamond', serif;
    font-size: 22px;
    font-weight: 700;
    color: #1a3a6b;
    line-height: 1.1;
  }
  .header .address {
    font-size: 11.5px;
    color: #666;
    margin-top: 2px;
    letter-spacing: .02em;
  }

  .divider {
    border: none;
    border-top: 2.5px solid #1a3a6b;
    margin: 10px 0 6px;
  }

  .form-title {
    text-align: center;
    font-family: 'EB Garamond', serif;
    font-size: 18px;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #1a3a6b;
    margin: 12px 0 18px;
  }

  .control-row {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 14px;
  }
  .control-row label {
    font-weight: 600;
    font-size: 12px;
    margin-right: 6px;
  }
  .control-row input {
    border: none;
    border-bottom: 1px solid #999;
    width: 140px;
    font-size: 12px;
    font-family: inherit;
    outline: none;
    padding: 0 2px;
    background: transparent;
  }

  .info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 16px;
  }
  .info-table td {
    border: 1px solid #bbb;
    padding: 5px 10px;
    font-size: 12.5px;
    vertical-align: middle;
  }
  .info-table td:first-child {
    background: #f4f6fb;
    font-weight: 600;
    width: 220px;
    white-space: nowrap;
  }
  .info-table input[type="text"],
  .info-table input[type="date"],
  .info-table input[type="tel"] {
    border: none;
    outline: none;
    width: 100%;
    font-family: inherit;
    font-size: 12.5px;
    background: transparent;
  }

  .section-label {
    font-weight: 700;
    font-size: 12px;
    margin-bottom: 6px;
    margin-top: 14px;
    color: #1a1a1a;
  }

  .submission-row {
    display: flex;
    align-items: center;
    gap: 24px;
    border: 1px solid #bbb;
    padding: 8px 14px;
    margin-bottom: 14px;
  }
  .submission-row span {
    font-weight: 600;
    font-size: 12.5px;
    margin-right: 8px;
  }
  .radio-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    cursor: pointer;
  }
  .radio-item input[type="radio"] {
    accent-color: #1a3a6b;
    width: 14px;
    height: 14px;
    cursor: pointer;
  }

  .request-type-row {
    display: flex;
    align-items: center;
    border: 1px solid #bbb;
    border-bottom: none;
    padding: 7px 14px;
    gap: 20px;
  }
  .request-type-row .rt-label {
    font-weight: 600;
    font-size: 12.5px;
    margin-right: 6px;
  }
  .check-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    cursor: pointer;
  }
  .check-item input[type="checkbox"] {
    accent-color: #1a3a6b;
    width: 14px;
    height: 14px;
    cursor: pointer;
  }

  .desc-label-row {
    border: 1px solid #bbb;
    border-bottom: none;
    border-top: none;
    padding: 5px 10px;
    font-size: 12px;
    color: #555;
    background: #f9f9f9;
    font-style: italic;
  }
  .desc-textarea {
    border: 1px solid #bbb;
    width: 100%;
    padding: 8px 10px;
    font-family: inherit;
    font-size: 12.5px;
    resize: vertical;
    outline: none;
    min-height: 72px;
    color: #1a1a1a;
    margin-bottom: 16px;
    display: block;
  }
  .desc-textarea:focus { border-color: #1a3a6b; }

  .approval-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin: 16px 0;
    padding: 0;
    gap: 32px;
  }
  .approval-check-group {
    display: flex;
    gap: 28px;
    align-items: center;
  }

  .inline-field {
    margin-bottom: 12px;
    font-size: 12.5px;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .inline-field label {
    white-space: nowrap;
    font-weight: 600;
  }
  .inline-field input[type="text"] {
    border: none;
    border-bottom: 1px solid #999;
    flex: 1;
    font-family: inherit;
    font-size: 12.5px;
    outline: none;
    padding: 0 2px;
    background: transparent;
  }

  .gsu-authority {
    text-align: center;
    margin-top: 6px;
    margin-bottom: 14px;
  }
  .gsu-authority .sig-line-long {
    border-bottom: 1px solid #999;
    margin: 0 auto 4px;
    width: 280px;
  }
  .gsu-authority .gsu-name-label {
    font-size: 11.5px;
    color: #555;
    font-style: italic;
  }

  .sig-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    border: 1px solid #bbb;
    margin-bottom: 16px;
  }
  .sig-block {
    padding: 10px 14px 14px;
    border-right: 1px solid #bbb;
  }
  .sig-block:last-child { border-right: none; }
  .sig-block-header {
    font-weight: 700;
    font-size: 11px;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: #1a3a6b;
    margin-bottom: 10px;
    border-bottom: 1.5px solid #1a3a6b;
    padding-bottom: 4px;
  }
  .sig-field {
    margin-bottom: 10px;
  }
  .sig-field label {
    display: block;
    font-size: 10.5px;
    color: #777;
    margin-bottom: 2px;
  }
  .sig-field .sig-line {
    border-bottom: 1px solid #999;
    min-height: 30px;
    width: 100%;
  }
  .sig-field input[type="text"] {
    border: none;
    border-bottom: 1px solid #999;
    width: 100%;
    font-family: inherit;
    font-size: 12px;
    outline: none;
    padding: 2px 2px;
    background: transparent;
  }

  .remarks-header {
    background: #1a3a6b;
    color: #fff;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: .04em;
    padding: 6px 10px;
    text-transform: uppercase;
  }
  .remarks-body {
    border: 1px solid #bbb;
    border-top: none;
    padding: 8px 10px;
    margin-bottom: 16px;
  }
  .remarks-body textarea {
    width: 100%;
    border: none;
    outline: none;
    font-family: inherit;
    font-size: 12.5px;
    resize: vertical;
    min-height: 60px;
    background: transparent;
  }

  .conforme-label {
    font-weight: 700;
    font-size: 12.5px;
    margin-bottom: 8px;
    margin-top: 4px;
  }

  .footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    font-size: 10.5px;
    color: #888;
    border-top: 1px solid #ddd;
    padding-top: 8px;
  }

  .submit-btn {
    display: block;
    margin: 20px auto 0;
    padding: 10px 24px;
    background: #1a3a6b;
    color: white;
    border: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border-radius: 4px;
  }
  .submit-btn:hover {
    background: #0f2847;
  }

  .error {
    color: #dc2626;
    font-size: 11px;
    margin-top: 2px;
  }

  @media print {
    body { background: white; padding: 0; }
    .page { box-shadow: none; }
    .submit-btn { display: none; }
  }
</style>
</head>
<body>
<div class="page">

  <!-- Header -->
  <div class="header">
    <div class="republic">Republic of the Philippines</div>
    <div class="university">UNIVERSITY OF ANTIQUE</div>
    <div class="address">Sibalom, Antique</div>
  </div>
  <hr class="divider">
  <div class="form-title">Repair and Maintenance Form</div>

  <form method="POST" action="{{ route('forms.repair.download') }}">
    @csrf

    <!-- Control No (auto-generated, displayed for reference) -->
    <div class="control-row">
      <label>Control No.:</label>
      <input type="text" value="Auto-generated" readonly style="background: #f0f0f0;">
    </div>

    <!-- Request Info -->
    <table class="info-table">
      <tr>
        <td>Date Request</td>
        <td>
          <input type="date" name="date_request" required value="{{ old('date_request') }}">
          @error('date_request') <div class="error">{{ $message }}</div> @enderror
        </td>
      </tr>
      <tr>
        <td>Requester Name &amp; Signature</td>
        <td>
          <input type="text" name="requester_name" required placeholder="Full name" value="{{ old('requester_name') }}">
          @error('requester_name') <div class="error">{{ $message }}</div> @enderror
        </td>
      </tr>
      <tr>
        <td>Department / Office</td>
        <td>
          <input type="text" name="department" required placeholder="Department or office name" value="{{ old('department') }}">
          @error('department') <div class="error">{{ $message }}</div> @enderror
        </td>
      </tr>
      <tr>
        <td>Requester Contact Number <span style="font-weight:400;color:#777;font-size:11px;">(Optional)</span></td>
        <td>
          <input type="tel" name="requester_contact" placeholder="Contact number" value="{{ old('requester_contact') }}">
        </td>
      </tr>
      <tr>
        <td>Maintenance / Repair Location</td>
        <td>
          <input type="text" name="repair_location" required placeholder="Location of maintenance or repair" value="{{ old('repair_location') }}">
          @error('repair_location') <div class="error">{{ $message }}</div> @enderror
        </td>
      </tr>
    </table>

    <!-- Request Submitted Via -->
    <div class="submission-row">
      <span>Request Submitted:</span>
      <label class="radio-item">
        <input type="radio" name="submission_method" value="in-person" {{ old('submission_method') == 'in-person' ? 'checked' : '' }}> In-person
      </label>
      <label class="radio-item">
        <input type="radio" name="submission_method" value="phone" {{ old('submission_method') == 'phone' ? 'checked' : '' }}> Phone
      </label>
    </div>

    <!-- Request Type -->
    <div class="request-type-row">
      <span class="rt-label">Requested:</span>
      <label class="check-item"><input type="checkbox" name="type_maintenance" {{ old('type_maintenance') ? 'checked' : '' }}> Maintenance</label>
      <label class="check-item"><input type="checkbox" name="type_repair" {{ old('type_repair') ? 'checked' : '' }}> Repair</label>
      <label class="check-item"><input type="checkbox" name="type_other" {{ old('type_other') ? 'checked' : '' }}> Other Services</label>
    </div>

    <!-- Description -->
    <div class="desc-label-row">Please enter details of work required and/or description of the problem:</div>
    <textarea class="desc-textarea" name="problem_description" required placeholder="Describe the work required or problem here...">{{ old('problem_description') }}</textarea>
    @error('problem_description') <div class="error">{{ $message }}</div> @enderror

    <!-- Approval / Disapproval (for office use) -->
    <div class="approval-row">
      <div class="approval-check-group">
        <label class="check-item" style="font-size:13px; font-weight:600;">
          <input type="checkbox" name="decision_approved" {{ old('decision_approved') ? 'checked' : '' }}> Approved
        </label>
        <label class="check-item" style="font-size:13px; font-weight:600;">
          <input type="checkbox" name="decision_disapproved" {{ old('decision_disapproved') ? 'checked' : '' }}> Disapproved
        </label>
      </div>
      <div class="gsu-authority">
        <div class="sig-line-long"></div>
        <div class="gsu-name-label">General Services Unit Head / Representative</div>
      </div>
    </div>

    <!-- Preferred Time -->
    <div class="inline-field">
      <label>Preferred time for maintenance/repair:</label>
      <input type="text" name="preferred_time" placeholder="e.g. 9:00 AM – 12:00 PM" value="{{ old('preferred_time') }}">
    </div>

    <!-- GSU Personnel (for office use) -->
    <div class="inline-field">
      <label>GSU Personnel:</label>
      <input type="text" name="gsu_personnel" placeholder="Name of assigned GSU personnel" value="{{ old('gsu_personnel') }}">
    </div>

    <!-- Maintenance Worker Signature Block (for office use) -->
    <div class="sig-grid-2" style="margin-top:10px;">
      <div class="sig-block" style="grid-column:1/-1; border-right:none; border-bottom:1px solid #bbb;">
        <div class="sig-block-header">GSU Personnel Sign-off</div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0;">
          <div class="sig-field" style="padding-right:20px;">
            <label>Signature of the Maintenance Worker</label>
            <div class="sig-line"></div>
          </div>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:0; border-left:1px solid #bbb; padding-left:14px;">
            <div class="sig-field" style="padding-right:12px;">
              <label>Name of the Maintenance Worker</label>
              <input type="text" name="worker_name" placeholder="Full name" value="{{ old('worker_name') }}">
            </div>
            <div class="sig-field" style="padding-left:12px; border-left:1px solid #eee;">
              <label>Date and Time</label>
              <input type="text" placeholder="Date and time" readonly>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Remarks (for office use) -->
    <div style="margin-bottom:16px;">
      <div class="remarks-header">Remarks: (Actual Work Done) <span style="font-size:10.5px; font-weight:400; font-style:italic; opacity:.8;">&mdash; To be filled up by Maintenance Personnel</span></div>
      <div class="remarks-body">
        <textarea name="remarks" placeholder="Describe the actual work done...">{{ old('remarks') }}</textarea>
      </div>
    </div>

    <!-- Conforme -->
    <div class="conforme-label">Conforme:</div>
    <div class="sig-grid-2">
      <div class="sig-block">
        <div class="sig-field">
          <label>Signature of the End-user</label>
          <div class="sig-line"></div>
        </div>
      </div>
      <div class="sig-block">
        <div class="sig-field" style="margin-bottom:4px;">
          <label>Name of the End-user</label>
          <input type="text" name="enduser_name" placeholder="Full name" value="{{ old('enduser_name', old('requester_name')) }}">
        </div>
        <div class="sig-field">
          <label>Date and Time</label>
          <input type="text" placeholder="Date and time" readonly>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="footer">
      <span>GSU-FM-011</span>
      <span>Rev.04 / 01-03-24</span>
    </div>

    <button type="submit" class="submit-btn">Generate PDF Form</button>
  </form>

</div>
</body>
</html>
