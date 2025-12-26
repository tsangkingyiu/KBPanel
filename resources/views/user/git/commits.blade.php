@extends('layouts.user')

@section('page-title', 'Commit History')
@section('page-subtitle', $repository->name)

@section('content')
<div class="space-y-6">
    <!-- Repository Info -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="h-12 w-12 bg-gray-900 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $repository->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $repository->url }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('user.git.repositories') }}" 
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Back to Repositories
                </a>
                <button onclick="syncCommits()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Sync Commits
                </button>
            </div>
        </div>
    </div>

    <!-- Branch Selector & Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <label for="branch-select" class="block text-sm font-medium text-gray-700 mb-2">Select Branch</label>
            <select id="branch-select" onchange="loadBranchCommits(this.value)"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @foreach($branches as $branch)
                    <option value="{{ $branch->name }}" {{ $selectedBranch == $branch->name ? 'selected' : '' }}>
                        {{ $branch->name }} {{ $branch->is_default ? '(default)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <p class="text-2xl font-bold text-blue-600">{{ $totalCommits }}</p>
                    <p class="text-sm text-gray-600 mt-1">Total Commits</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-green-600">{{ $contributors }}</p>
                    <p class="text-sm text-gray-600 mt-1">Contributors</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-purple-600">{{ $branches->count() }}</p>
                    <p class="text-sm text-gray-600 mt-1">Branches</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Commit Timeline -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Commit History</h3>
        </div>

        @if($commits->isEmpty())
        <div class="p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Commits Found</h3>
            <p class="text-gray-600">This branch doesn't have any commits yet</p>
        </div>
        @else
        <div class="p-6">
            <div class="space-y-6">
                @foreach($commits as $commit)
                <div class="relative pl-8 pb-6 border-l-2 border-gray-200 last:border-0 last:pb-0">
                    <div class="absolute -left-2 top-0 w-4 h-4 rounded-full bg-blue-500 border-2 border-white"></div>
                    
                    <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h4 class="text-base font-semibold text-gray-900">{{ $commit->message }}</h4>
                                <div class="flex items-center mt-2 space-x-4 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($commit->author_name) }}&background=2563EB&color=fff&size=24" 
                                             class="w-6 h-6 rounded-full mr-2" alt="{{ $commit->author_name }}">
                                        <span>{{ $commit->author_name }}</span>
                                    </div>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $commit->committed_at->diffForHumans() }}
                                    </span>
                                </div>
                                
                                @if($commit->files_changed > 0)
                                <div class="mt-3 flex items-center space-x-4 text-xs text-gray-500">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $commit->files_changed }} files changed
                                    </span>
                                    <span class="text-green-600">+{{ $commit->additions }} additions</span>
                                    <span class="text-red-600">-{{ $commit->deletions }} deletions</span>
                                </div>
                                @endif
                            </div>
                            
                            <div class="ml-4 flex-shrink-0 flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-mono bg-gray-200 text-gray-700">
                                    {{ substr($commit->sha, 0, 7) }}
                                </span>
                                <button onclick="viewCommitDetails('{{ $commit->sha }}')" 
                                        class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                                @if($commit->can_revert)
                                <button onclick="revertCommit('{{ $commit->sha }}')" 
                                        class="p-2 text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        @if($commits->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $commits->links() }}
        </div>
        @endif
        @endif
    </div>
</div>

<script>
function loadBranchCommits(branch) {
    window.location.href = `?branch=${branch}`;
}

function syncCommits() {
    if(confirm('Sync commits from remote repository?')) {
        fetch(`/user/git/repositories/{{ $repository->id }}/sync-commits`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(response => response.json())
          .then(data => {
              alert(data.message);
              location.reload();
          });
    }
}

function viewCommitDetails(sha) {
    window.location.href = `/user/git/repositories/{{ $repository->id }}/commits/${sha}`;
}

function revertCommit(sha) {
    if(confirm('Are you sure you want to revert this commit? This will create a new commit that undoes these changes.')) {
        fetch(`/user/git/repositories/{{ $repository->id }}/commits/${sha}/revert`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(response => response.json())
          .then(data => {
              alert(data.message);
              location.reload();
          });
    }
}
</script>
@endsection
