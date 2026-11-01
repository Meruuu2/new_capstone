<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Facilities and Utilization Form – University of Antique</title>
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
  .info-table tr td {
    border: 1px solid #bbb;
    padding: 5px 10px;
    font-size: 12.5px;
    vertical-align: middle;
  }
  .info-table tr td:first-child {
    background: #f4f6fb;
    font-weight: 600;
    width: 220px;
    white-space: nowrap;
  }
  .info-table input[type="text"],
  .info-table input[type="date"],
  .info-table input[type="time"],
  .info-table input[type="tel"] {
    border: none;
    outline: none;
    width: 100%;
    font-family: inherit;
    font-size: 12.5px;
    background: transparent;
  }

  .section-header {
    background: #1a3a6b;
    color: #fff;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: .05em;
    text-transform: uppercase;
    padding: 6px 10px;
    margin-top: 16px;
    margin-bottom: 0;
  }

  .venues-box {
    border: 1px solid #bbb;
    border-top: none;
    padding: 12px 14px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px 16px;
    margin-bottom: 16px;
  }
  .venue-item {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 12.5px;
  }
  .venue-item input[type="checkbox"] {
    width: 14px;
    height: 14px;
    accent-color: #1a3a6b;
    flex-shrink: 0;
    cursor: pointer;
  }
  .venue-others {
    grid-column: 1 / -1;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12.5px;
  }
  .venue-others input[type="text"] {
    border: none;
    border-bottom: 1px solid #999;
    flex: 1;
    font-family: inherit;
    font-size: 12.5px;
    outline: none;
    padding: 0 2px;
    background: transparent;
  }

  .facilities-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 16px;
  }
  .facilities-table th {
    background: #f4f6fb;
    border: 1px solid #bbb;
    padding: 6px 10px;
    font-size: 12px;
    font-weight: 700;
    text-align: left;
  }
  .facilities-table td {
    border: 1px solid #bbb;
    padding: 5px 10px;
    font-size: 12.5px;
    vertical-align: middle;
  }
  .facilities-table td:last-child {
    width: 120px;
  }
  .facilities-table input[type="number"] {
    border: none;
    outline: none;
    width: 100%;
    font-family: inherit;
    font-size: 12.5px;
    background: transparent;
    text-align: center;
  }

  .sig-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    margin-top: 16px;
    border: 1px solid #bbb;
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
  <div class="form-title">Facilities and Utilization Form</div>

  <form method="POST" action="{{ route('forms.facilities.download') }}">
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
        <td>Requester Name and Signature</td>
        <td>
          <input type="text" name="requester_name" required placeholder="Full name" value="{{ old('requester_name') }}">
          @error('requester_name') <div class="error">{{ $message }}</div> @enderror
        </td>
      </tr>
      <tr>
        <td>Requester Contact Number <span style="font-weight:400;color:#777;font-size:11px;">(Optional)</span></td>
        <td>
          <input type="tel" name="requester_contact" placeholder="Contact number" value="{{ old('requester_contact') }}">
        </td>
      </tr>
      <tr>
        <td>Date of Activity</td>
        <td>
          <input type="date" name="date_activity" required value="{{ old('date_activity') }}">
          @error('date_activity') <div class="error">{{ $message }}</div> @enderror
        </td>
      </tr>
      <tr>
        <td>Time of Activity</td>
        <td>
          <input type="text" name="time_activity" required placeholder="e.g. 8:00 AM – 5:00 PM" value="{{ old('time_activity') }}">
          @error('time_activity') <div class="error">{{ $message }}</div> @enderror
        </td>
      </tr>
      <tr>
        <td>Purpose</td>
        <td>
          <input type="text" name="purpose" required placeholder="Describe the purpose of the activity" value="{{ old('purpose') }}">
          @error('purpose') <div class="error">{{ $message }}</div> @enderror
        </td>
      </tr>
    </table>

    <!-- Venues -->
    <div class="section-header">Venues to be Utilized:</div>
    <div class="venues-box">
      <label class="venue-item"><input type="checkbox" name="busalian_hall" {{ old('busalian_hall') ? 'checked' : '' }}> BUSALIAN HALL</label>
      <label class="venue-item"><input type="checkbox" name="paghiusa_hall" {{ old('paghiusa_hall') ? 'checked' : '' }}> PAGHIUSA HALL</label>

      <label class="venue-item"><input type="checkbox" name="e_hub" {{ old('e_hub') ? 'checked' : '' }}> E-HUB</label>
      <label class="venue-item"><input type="checkbox" name="balay_ni_juan" {{ old('balay_ni_juan') ? 'checked' : '' }}> BALAY NI JUAN</label>
      <label class="venue-item"><input type="checkbox" name="ict_avr" {{ old('ict_avr') ? 'checked' : '' }}> ICT AVR</label>
      <label class="venue-item"><input type="checkbox" name="cea_avr" {{ old('cea_avr') ? 'checked' : '' }}> CEA AVR</label>
      <label class="venue-item"><input type="checkbox" name="cba_avr" {{ old('cba_avr') ? 'checked' : '' }}> CBA AVR</label>
      <label class="venue-item"><input type="checkbox" name="new_avr" {{ old('new_avr') ? 'checked' : '' }}> NEW AVR</label>
      <label class="venue-item"><input type="checkbox" name="grand_stand" {{ old('grand_stand') ? 'checked' : '' }}> GRAND STAND</label>
      <label class="venue-item"><input type="checkbox" name="covered_gym" {{ old('covered_gym') ? 'checked' : '' }}> COVERED GYM</label>
      <label class="venue-item"><input type="checkbox" name="track_oval" {{ old('track_oval') ? 'checked' : '' }}> TRACK OVAL</label>
      <div class="venue-others">
        <label class="venue-item" style="flex-shrink:0"><input type="checkbox" name="other_venue" {{ old('other_venue') ? 'checked' : '' }}> Others</label>
        <span style="font-size:11.5px;color:#666;flex-shrink:0">Please specify:</span>
        <input type="text" name="venue_others" placeholder="___________________________" value="{{ old('venue_others') }}">
      </div>
    </div>

    <!-- Facilities -->
    <div class="section-header">Facility/ies to be Used:</div>
    <table class="facilities-table" style="border-top:none;">
      <thead>
        <tr>
          <th>Item</th>
          <th style="text-align:center;">Quantity</th>
        </tr>
      </thead>
      <tbody>
      <tr><td>Monobloc Chair</td><td><input type="number" name="qty_monobloc" min="0" placeholder="0" value="{{ old('qty_monobloc') }}"></td></tr>
      <tr><td>Long Table</td><td><input type="number" name="qty_table" min="0" placeholder="0" value="{{ old('qty_table') }}"></td></tr>
      <tr><td>Electric Fan</td><td><input type="number" name="qty_fan" min="0" placeholder="0" value="{{ old('qty_fan') }}"></td></tr>
      <tr><td>Rostrum</td><td><input type="number" name="qty_rostrum" min="0" placeholder="0" value="{{ old('qty_rostrum') }}"></td></tr>
      <tr><td>Flag &amp; School Color</td><td><input type="number" name="qty_flag" min="0" placeholder="0" value="{{ old('qty_flag') }}"></td></tr>
      <tr><td>Sound</td><td><input type="number" name="qty_sound" min="0" placeholder="0" value="{{ old('qty_sound') }}"></td></tr>
      <tr><td>LED Wall</td><td><input type="number" name="qty_led" min="0" placeholder="0" value="{{ old('qty_led') }}"></td></tr>
    </tbody>
    </table>

    <!-- Signature Blocks (for office use after approval) -->
    <div class="sig-grid">
      <div class="sig-block">
        <div class="sig-block-header">Requested By:</div>
        <div class="sig-field">
          <label>Signature of Requisitioner</label>
          <div class="sig-line"></div>
        </div>
        <div class="sig-field">
          <label>Name of Requisitioner</label>
          <input type="text" name="req_name" placeholder="Full name" value="{{ old('req_name', old('requester_name')) }}">
        </div>
        <div class="sig-field">
          <label>Date and Time</label>
          <input type="text" placeholder="Date and time" readonly>
        </div>
      </div>
      <div class="sig-block">
        <div class="sig-block-header">Noted By:</div>
        <div class="sig-field">
          <label>Signature of the Head of Office</label>
          <div class="sig-line"></div>
        </div>
        <div class="sig-field">
          <label>Name of the Head of Office</label>
          <input type="text" name="noted_name" placeholder="Full name" value="{{ old('noted_name') }}">
        </div>
        <div class="sig-field">
          <label>Date and Time</label>
          <input type="text" placeholder="Date and time" readonly>
        </div>
      </div>
      <div class="sig-block">
        <div class="sig-block-header">Approved By:</div>
        <div class="sig-field">
          <label>Signature of GSU-Person in Charge</label>
          <div class="sig-line"></div>
        </div>
        <div class="sig-field">
          <label>Name of GSU-Person in Charge</label>
          <input type="text" name="approved_name" placeholder="Full name" value="{{ old('approved_name') }}">
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
