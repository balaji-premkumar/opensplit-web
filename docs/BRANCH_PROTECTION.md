# GitHub Branch Protection Setup

This document explains how to protect the master branch from direct edits and require PR approvals.

## Prerequisites

1. Push your code to GitHub
2. The CI workflow must run at least once
3. You must have admin access to the repository

---

## Master Branch Protection Rules

### Step 1: Navigate to Settings

1. Go to your repository on GitHub
2. Click **Settings** → **Branches**
3. Click **Add branch protection rule**

### Step 2: Configure Protection

| Setting | Value | Description |
|---------|-------|-------------|
| **Branch name pattern** | `master` | Protects the master branch |
| **Require a pull request before merging** | ✅ | No direct pushes allowed |
| **Require approvals** | ✅ (1 or more) | Requires reviewer approval |
| **Dismiss stale PR approvals** | ✅ | Re-approve if code changes |
| **Require status checks to pass** | ✅ | Tests must pass |
| **Require branches to be up to date** | ✅ | Must merge latest master first |
| **Require conversation resolution** | ✅ Optional | All comments must be resolved |
| **Do not allow bypassing** | ✅ | Even admins must follow rules |

### Step 3: Select Required Status Checks

Search and select:
- **Run Tests**
- **Code Quality**

### Step 4: Lock Branch (Optional)

To completely prevent any changes:
- ✅ **Lock branch** - Makes branch read-only

---

## Workflow Summary

```
Developer
    │
    ├──✗── Direct push to master (BLOCKED)
    │
    └──✓── Create feature branch
              │
              ▼
         Create Pull Request
              │
              ▼
    ┌─────────────────────────────────────┐
    │  Automatic Checks                   │
    │  ├── CI: Run Tests (must pass)      │
    │  └── CI: Code Quality (must pass)   │
    └─────────────────────────────────────┘
              │
              ▼
         Reviewer Approval Required
              │
              ▼
         Merge to Master ✓
```

---

## GitHub Actions Workflows

### 1. CI Workflow (Automatic)

**File:** `.github/workflows/ci.yml`

**Triggers:**
- Pull requests to `main`, `master`, `develop`
- Pushes to `main`, `master`, `develop`

**Jobs:**
- `test` - Runs PHPUnit tests with PostgreSQL
- `lint` - Checks PHP syntax

### 2. Docker Build (Manual)

**File:** `.github/workflows/docker-build.yml`

**Trigger:** Manual (`workflow_dispatch`)

**How to Run:**
1. Go to **Actions** tab
2. Select **Docker Build**
3. Click **Run workflow**
4. Fill in inputs:
   - **Image tag**: e.g., `latest`, `v1.0.0`
   - **Push to registry**: Yes/No
   - **Environment**: development/staging/production

**Required Secrets (for pushing):**
- `DOCKERHUB_USERNAME` - Your Docker Hub username
- `DOCKERHUB_TOKEN` - Docker Hub access token

---

## Quick Setup Checklist

- [ ] Push code to GitHub
- [ ] Go to Settings → Branches
- [ ] Add protection rule for `master`
- [ ] Enable "Require pull request"
- [ ] Enable "Require approvals" (set to 1+)
- [ ] Enable "Require status checks"
- [ ] Select "Run Tests" as required check
- [ ] Save the rule
- [ ] (Optional) Add Docker Hub secrets for image pushing

---

## Troubleshooting

### "Status checks not found"
- Ensure CI workflow has run at least once on a PR

### "Cannot push to master"
- This is expected! Create a branch and open a PR

### Docker build secrets missing
- Go to Settings → Secrets → Actions
- Add `DOCKERHUB_USERNAME` and `DOCKERHUB_TOKEN`
