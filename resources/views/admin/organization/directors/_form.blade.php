<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="name">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $director->name) }}" required>
                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="email">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $director->email) }}" required>
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="password">Password @unless($director->exists)<span class="text-danger">*</span>@endunless</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" @unless($director->exists) required @endunless>
                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password @unless($director->exists)<span class="text-danger">*</span>@endunless</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" @unless($director->exists) required @endunless>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ $submitLabel }}</button>
        <a href="{{ route('admin.organization.directors.index') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
    </div>
</div>
