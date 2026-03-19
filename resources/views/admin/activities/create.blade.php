@extends('layouts.admin')

@section('title', 'Create Event')

@section('content')
    <div class="top-bar">
        <h1>Create New Event</h1>
        <a href="{{ route('admin.activities.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to list
        </a>
    </div>

    <div class="card">
        <form action="{{ route('admin.activities.store') }}" method="POST">
            @csrf
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
                <div>
                    <div class="form-group">
                        <label for="title">Event Title</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" required>
                        @error('title') <div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="description_html">Description (HTML allowed)</label>
                        <textarea id="description_html" name="description_html" rows="10" required>{{ old('description_html') }}</textarea>
                        @error('description_html') <div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="activity_subtype">Subtype (e.g., Open Access, Route)</label>
                            <input type="text" id="activity_subtype" name="activity_subtype" value="{{ old('activity_subtype') }}">
                        </div>
                        <div class="form-group">
                            <label for="live_stream_url">Live Stream URL (YouTube)</label>
                            <input type="url" id="live_stream_url" name="live_stream_url" value="{{ old('live_stream_url') }}">
                        </div>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label for="type">Category</label>
                        <select id="type" name="type" required>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ old('type') === $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="age_group">Age Group</label>
                        <input type="text" id="age_group" name="age_group" value="{{ old('age_group') }}" placeholder="e.g. 5-10 years" required>
                    </div>

                    <div class="form-group">
                        <label for="location">Venue</label>
                        <select id="location" name="location" required>
                            @foreach($venues as $venue)
                                <option value="{{ $venue }}" {{ old('location') === $venue ? 'selected' : '' }}>{{ $venue }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="capacity">Total Capacity</label>
                            <input type="number" id="capacity" name="capacity" value="{{ old('capacity', 20) }}" required min="1">
                        </div>
                        <div class="form-group">
                            <label for="fee">Fee (€)</label>
                            <input type="number" step="0.01" id="fee" name="fee" value="{{ old('fee', 0) }}" required min="0">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="seating_capacity">Seating Capacity</label>
                            <input type="number" id="seating_capacity" name="seating_capacity" value="{{ old('seating_capacity') }}">
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end; padding-bottom: 12px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                                <input type="checkbox" name="numbered_seating" value="1" {{ old('numbered_seating') ? 'checked' : '' }} style="width: auto;">
                                Numbered Seats
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="start_at">Start Date & Time</label>
                        <input type="datetime-local" id="start_at" name="start_at" value="{{ old('start_at') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="end_at">End Date & Time</label>
                        <input type="datetime-local" id="end_at" name="end_at" value="{{ old('end_at') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="reg_start_at">Registrations Open At</label>
                        <input type="datetime-local" id="reg_start_at" name="reg_start_at" value="{{ old('reg_start_at') }}">
                    </div>

                    <div class="form-group">
                        <label for="start_time_label">Registration Label (e.g. Opening Soon)</label>
                        <input type="text" id="start_time_label" name="start_time_label" value="{{ old('start_time_label') }}">
                    </div>

                    <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} style="width: auto;">
                            Is Active
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="waitlist_enabled" value="1" {{ old('waitlist_enabled', true) ? 'checked' : '' }} style="width: auto;">
                            Waitlist
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="requires_selection" value="1" {{ old('requires_selection') ? 'checked' : '' }} style="width: auto;">
                            Manual Selection
                        </label>
                    </div>

                    <div style="margin-top: 32px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Create Event</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
