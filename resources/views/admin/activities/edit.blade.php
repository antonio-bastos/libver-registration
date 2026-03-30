@extends('layouts.admin')

@section('title', 'Edit Event')

@section('content')
    <div class="top-bar">
        <h1>Edit: {{ $activity->title }}</h1>
        <a href="{{ route('admin.activities.index') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Back to list
        </a>
    </div>

    <div class="card">
        <form action="{{ route('admin.activities.update', $activity) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px;">
                <div>
                    <div class="form-group">
                        <label for="title">Event Title</label>
                        <input type="text" id="title" name="title" value="{{ old('title', $activity->title) }}" required>
                        @error('title') <div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="description_html">Description (HTML allowed)</label>
                        <textarea id="description_html" name="description_html" rows="10" required>{{ old('description_html', $activity->description_html) }}</textarea>
                        @error('description_html') <div style="color: var(--danger); font-size: 12px; margin-top: 4px;">{{ $message }}</div> @enderror
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="activity_subtype">Subtype (e.g., Open Access, Route)</label>
                            <input type="text" id="activity_subtype" name="activity_subtype" value="{{ old('activity_subtype', $activity->activity_subtype) }}">
                        </div>
                        <div class="form-group">
                            <label for="online_url">Online Meeting URL</label>
                            <input type="url" id="online_url" name="online_url" value="{{ old('online_url', $activity->online_url) }}">
                        </div>
                        <div class="form-group">
                            <label for="live_stream_url">Live Stream URL (YouTube)</label>
                            <input type="url" id="live_stream_url" name="live_stream_url" value="{{ old('live_stream_url', $activity->live_stream_url) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="connection_details">Online Connection Instructions</label>
                        <textarea id="connection_details" name="connection_details" rows="3" placeholder="Optional login code, Zoom instructions, host notes...">{{ old('connection_details', $activity->connection_details) }}</textarea>
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label for="type">Category</label>
                        <select id="type" name="type" required>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ old('type', $activity->type) === $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="age_group">Age Group</label>
                        <input type="text" id="age_group" name="age_group" value="{{ old('age_group', $activity->age_group) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="location">Venue</label>
                        <select id="location" name="location" required>
                            @foreach($venues as $venue)
                                <option value="{{ $venue }}" {{ old('location', $activity->location) === $venue ? 'selected' : '' }}>{{ $venue }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="capacity">Total Capacity</label>
                            <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $activity->capacity) }}" required min="1">
                        </div>
                        <div class="form-group">
                            <label for="fee">Fee (€)</label>
                            <input type="number" step="0.01" id="fee" name="fee" value="{{ old('fee', $activity->fee) }}" required min="0">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-group">
                            <label for="seating_capacity">Seating Capacity</label>
                            <input type="number" id="seating_capacity" name="seating_capacity" value="{{ old('seating_capacity', $activity->seating_capacity) }}">
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end; padding-bottom: 12px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                                <input type="checkbox" name="numbered_seating" value="1" {{ old('numbered_seating', $activity->numbered_seating) ? 'checked' : '' }} style="width: auto;">
                                Numbered Seats
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="start_at">Start Date & Time</label>
                        <input type="datetime-local" id="start_at" name="start_at" value="{{ old('start_at', $activity->start_at->format('Y-m-d\TH:i')) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="end_at">End Date & Time</label>
                        <input type="datetime-local" id="end_at" name="end_at" value="{{ old('end_at', $activity->end_at->format('Y-m-d\TH:i')) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="reg_start_at">Registrations Open At</label>
                        <input type="datetime-local" id="reg_start_at" name="reg_start_at" value="{{ old('reg_start_at', $activity->reg_start_at ? $activity->reg_start_at->format('Y-m-d\TH:i') : '') }}">
                    </div>

                    <div class="form-group">
                        <label for="start_time_label">Registration Label (e.g. Opening Soon)</label>
                        <input type="text" id="start_time_label" name="start_time_label" value="{{ old('start_time_label', $activity->start_time_label) }}">
                    </div>

                    <div class="form-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $activity->is_active) ? 'checked' : '' }} style="width: auto;">
                            Is Active
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="waitlist_enabled" value="1" {{ old('waitlist_enabled', $activity->waitlist_enabled) ? 'checked' : '' }} style="width: auto;">
                            Waitlist
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="requires_selection" value="1" {{ old('requires_selection', $activity->requires_selection) ? 'checked' : '' }} style="width: auto;">
                            Selection Later (Unlimited Interest)
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="first_timers_only" value="1" {{ old('first_timers_only', $activity->first_timers_only) ? 'checked' : '' }} style="width: auto;">
                            First Timers Only
                        </label>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="is_space_booking" value="1" {{ old('is_space_booking', $activity->is_space_booking) ? 'checked' : '' }} style="width: auto;">
                            Space Booking
                        </label>
                    </div>

                    <div style="margin-top: 32px;">
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Update Event</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
