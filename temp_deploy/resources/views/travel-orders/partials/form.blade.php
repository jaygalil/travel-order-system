@php
  $isEdit = isset($travelOrder);
@endphp

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // --- State ---
    let allUsers = [];
    let selectedParticipants = [];
    let filteredUsers = [];
    let currentPage = 1;
    let pageSize = 10;

    // --- Inline user selector (no modal) ---
    function showUserSelector() {
      const panel = document.getElementById('userSelectorPanel');
      if (panel) {
        panel.classList.remove('d-none');
        if (allUsers.length === 0) loadUsers();
      }
    }

    function hideUserSelector() {
      const panel = document.getElementById('userSelectorPanel');
      if (panel) panel.classList.add('d-none');
    }

    function addSelectedUsers() {
      const checkboxes = document.querySelectorAll('#userSelectorPanel .user-checkbox:checked:not(:disabled)');
      checkboxes.forEach(checkbox => {
        const userData = JSON.parse(checkbox.dataset.user);
        addParticipant(userData);
      });
      updateParticipantsDisplay();
      hideUserSelector();
    }

    // --- Data loading ---
    function loadUsers() {
      fetch('/api/users')
        .then(response => {
          if (!response.ok) throw new Error('Network response was not ok');
          return response.json();
        })
        .then(data => {
          allUsers = data;
          filteredUsers = allUsers;
          renderUsersPage();
          populateDepartmentFilter(allUsers);
        })
        .catch(error => {
          console.error('Error loading users from API, using fallback:', error);
          loadFallbackUsers();
        });
    }

    function loadFallbackUsers() {
      @php
        $fallbackUsers = App\Models\User::select('id', 'name', 'email', 'position', 'division_agency', 'phone', 'employee_id')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'position' => $user->position ?? 'N/A',
                    'division_agency' => $user->division_agency ?? 'N/A',
                    'phone' => $user->phone ?? '',
                    'employee_id' => $user->employee_id ?? '',
                    'display' => $user->name . ' (' . $user->email . ')'
                ];
            });
      @endphp
      allUsers = @json($fallbackUsers);
      filteredUsers = allUsers;
      renderUsersPage();
      populateDepartmentFilter(allUsers);
    }

    // --- UI builders ---
    function populateUsersTable(users) {
      const tbody = document.getElementById('usersTableBodyInline');
      if (!tbody) return;
      tbody.innerHTML = '';
      users.forEach(user => {
        const isSelected = selectedParticipants.some(p => p.id === user.id);
        const row = document.createElement('tr');
        row.classList.toggle('table-secondary', isSelected);
        row.innerHTML = `
          <td>
            <input type="checkbox" class="user-checkbox" value="${user.id}" data-user='${JSON.stringify(user)}' ${isSelected ? 'checked disabled' : ''}>
          </td>
          <td>
            <div class="d-flex align-items-center">
              <i class="fas fa-user-circle fa-lg text-primary me-2"></i>
              <div>
                <strong>${user.name}</strong>
                ${user.employee_id ? `<br><small class="text-muted">${user.employee_id}</small>` : ''}
              </div>
            </div>
          </td>
          <td>${user.position || 'N/A'}</td>
          <td>${user.division_agency || 'N/A'}</td>
          <td>
            ${user.email}
            ${user.phone ? `<br><small class="text-muted">${user.phone}</small>` : ''}
          </td>
        `;
        tbody.appendChild(row);
      });
    }

    function populateDepartmentFilter(users) {
      const departments = [...new Set(users.map(u => u.division_agency).filter(Boolean))];
      const select = document.getElementById('departmentFilterInline');
      if (!select) return;
      select.innerHTML = '<option value="">All Departments</option>';
      departments.forEach(dept => {
        const option = document.createElement('option');
        option.value = dept;
        option.textContent = dept;
        select.appendChild(option);
      });
    }

    function renderUsersPage() {
      const total = filteredUsers.length;
      const totalPages = Math.max(1, Math.ceil(total / pageSize));
      if (currentPage > totalPages) currentPage = totalPages;
      const start = (currentPage - 1) * pageSize;
      const end = start + pageSize;
      const pageItems = filteredUsers.slice(start, end);
      populateUsersTable(pageItems);
      updatePaginationControls(totalPages, total);
    }

    function updatePaginationControls(totalPages, total) {
      const pageInfo = document.getElementById('pageInfoInline');
      const prevBtn = document.getElementById('prevPageInline');
      const nextBtn = document.getElementById('nextPageInline');
      const sizeSel = document.getElementById('pageSizeInline');
      if (pageInfo) pageInfo.textContent = `Page ${currentPage} of ${totalPages} • ${total} users`;
      if (prevBtn) prevBtn.disabled = currentPage <= 1;
      if (nextBtn) nextBtn.disabled = currentPage >= totalPages;
      if (sizeSel && parseInt(sizeSel.value, 10) !== pageSize) sizeSel.value = String(pageSize);
      const selectAll = document.getElementById('selectAllInline');
      if (selectAll) selectAll.checked = false;
    }

    // --- Participants handling ---
    function addParticipant(userData) {
      if (selectedParticipants.some(p => p.id === userData.id)) return;
      
      // Ensure all required fields have values
      const participant = {
        id: userData.id,
        user_id: userData.id,
        employee_name: userData.name || userData.employee_name || 'Unknown',
        employee_id: userData.employee_id || '',
        position: userData.position || 'Staff',
        division_agency: userData.division_agency || userData.department || 'Not Specified',
        phone: userData.phone || '',
        email: userData.email || '',
        is_primary: selectedParticipants.length === 0,
        special_requirements: ''
      };
      
      console.log('Adding participant with data:', participant);
      selectedParticipants.push(participant);
    }

    function removeParticipant(userId) {
      if (selectedParticipants.length <= 1) {
        alert('At least one participant is required.');
        return;
      }
      const index = selectedParticipants.findIndex(p => p.id == userId);
      if (index > -1) {
        const wasPrimary = selectedParticipants[index].is_primary;
        selectedParticipants.splice(index, 1);
        if (wasPrimary && selectedParticipants.length > 0) {
          selectedParticipants[0].is_primary = true;
        }
        updateParticipantsDisplay();
      }
    }

    function makePrimary(userId) {
      selectedParticipants.forEach(p => { p.is_primary = p.id == userId; });
      updateParticipantsDisplay();
    }

    function updateParticipantsDisplay() {
      const tbody = document.getElementById('participants-tbody');
      const noParticipants = document.getElementById('no-participants');

      // Clear existing hidden inputs
      const existingInputs = document.querySelectorAll('input[name^="participants["]');
      existingInputs.forEach(input => input.remove());

      if (selectedParticipants.length === 0) {
        tbody.innerHTML = '';
        noParticipants.style.display = 'block';
        return;
      }

      noParticipants.style.display = 'none';
      tbody.innerHTML = '';

      selectedParticipants.forEach((participant, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${index + 1}</td>
          <td>
            <div class="d-flex align-items-center">
              <i class="fas fa-user-circle fa-lg text-primary me-2"></i>
              <div>
                <strong>${participant.employee_name}</strong>
                ${participant.employee_id ? `<br><small class="text-muted">${participant.employee_id}</small>` : ''}
              </div>
            </div>
          </td>
          <td>${participant.position || 'N/A'}</td>
          <td>${participant.division_agency || 'N/A'}</td>
          <td>
            ${participant.email || 'N/A'}
            ${participant.phone ? `<br><small class="text-muted">${participant.phone}</small>` : ''}
          </td>
          <td>
            ${participant.is_primary ? 
              '<span class="badge bg-primary">Primary</span>' : 
              `<button type="button" class="btn btn-sm btn-outline-primary" onclick="makePrimary(${participant.id})" title="Make Primary"><i class="fas fa-star"></i></button>`
            }
          </td>
          <td>
            ${selectedParticipants.length > 1 && !participant.is_primary ? 
              `<button type="button" class="btn btn-sm btn-danger" onclick="removeParticipant(${participant.id})" title="Remove"><i class="fas fa-trash"></i></button>` : 
              '<span class="text-muted">-</span>'
            }
          </td>
        `;
        tbody.appendChild(row);
      });

      // Get the hidden inputs container
      let hiddenContainer = document.getElementById('participants-hidden-inputs');
      if (!hiddenContainer) {
        console.error('Hidden inputs container not found! This will cause submission issues.');
        return;
      }

      // Clear and rebuild hidden inputs
      hiddenContainer.innerHTML = '';
      console.log('Rebuilding hidden inputs for', selectedParticipants.length, 'participants');
      selectedParticipants.forEach((participant, index) => {
        console.log('Creating inputs for participant', index, ':', participant.employee_name);
        
        // Ensure required fields have values (use defaults if empty)
        const safeEmployeeName = participant.employee_name || 'Unknown';
        const safePosition = participant.position || 'Staff';
        const safeDivision = participant.division_agency || 'Not Specified';
        
        const inputsHtml = `
          <input type="hidden" name="participants[${index}][user_id]" value="${participant.user_id || ''}">
          <input type="hidden" name="participants[${index}][employee_name]" value="${safeEmployeeName}">
          <input type="hidden" name="participants[${index}][employee_id]" value="${participant.employee_id || ''}">
          <input type="hidden" name="participants[${index}][position]" value="${safePosition}">
          <input type="hidden" name="participants[${index}][division_agency]" value="${safeDivision}">
          <input type="hidden" name="participants[${index}][phone]" value="${participant.phone || ''}">
          <input type="hidden" name="participants[${index}][email]" value="${participant.email || ''}">
          <input type="hidden" name="participants[${index}][is_primary]" value="${participant.is_primary ? 1 : 0}">
          <input type="hidden" name="participants[${index}][special_requirements]" value="${participant.special_requirements || ''}">
        `;
        hiddenContainer.innerHTML += inputsHtml;
        
        console.log('Created inputs with values:', {
          name: safeEmployeeName,
          position: safePosition,
          division: safeDivision
        });
      });
    }

    function setupSearch() {
      const searchInput = document.getElementById('userSearchInline');
      const departmentFilter = document.getElementById('departmentFilterInline');
      const sizeSel = document.getElementById('pageSizeInline');
      const prevBtn = document.getElementById('prevPageInline');
      const nextBtn = document.getElementById('nextPageInline');

      function filterUsers() {
        const searchTerm = (searchInput?.value || '').toLowerCase();
        const selectedDept = departmentFilter?.value || '';
        filteredUsers = allUsers.filter(user => {
          const matchesSearch = !searchTerm ||
            user.name.toLowerCase().includes(searchTerm) ||
            user.email.toLowerCase().includes(searchTerm) ||
            (user.position && user.position.toLowerCase().includes(searchTerm));
          const matchesDept = !selectedDept || user.division_agency === selectedDept;
          return matchesSearch && matchesDept;
        });
        currentPage = 1;
        renderUsersPage();
      }

      if (searchInput) searchInput.addEventListener('input', filterUsers);
      if (departmentFilter) departmentFilter.addEventListener('change', filterUsers);
      if (sizeSel) sizeSel.addEventListener('change', function() {
        const val = parseInt(this.value, 10);
        pageSize = isNaN(val) ? 10 : val;
        currentPage = 1;
        renderUsersPage();
      });
      if (prevBtn) prevBtn.addEventListener('click', function(){ if (currentPage > 1) { currentPage--; renderUsersPage(); } });
      if (nextBtn) nextBtn.addEventListener('click', function(){ currentPage++; renderUsersPage(); });
    }

    function setupSelectAll() {
      const selectAllCheckbox = document.getElementById('selectAllInline');
      if (!selectAllCheckbox) return;
      selectAllCheckbox.addEventListener('change', function() {
        const userCheckboxes = document.querySelectorAll('#userSelectorPanel .user-checkbox:not(:disabled)');
        userCheckboxes.forEach(cb => { cb.checked = this.checked; });
      });
    }

    // Expose for inline handlers
    window.showUserSelector = showUserSelector;
    window.hideUserSelector = hideUserSelector;
    window.addSelectedUsers = addSelectedUsers;
    window.removeParticipant = removeParticipant;
    window.makePrimary = makePrimary;

    // --- Initial Page Load Logic ---
    @if($isEdit && isset($travelOrder) && $travelOrder->participants->count() > 0)
      const existingParticipants = @json($travelOrder->participants);
      selectedParticipants = existingParticipants.map(p => ({
        id: p.user_id || Math.random(), user_id: p.user_id, employee_name: p.employee_name,
        employee_id: p.employee_id, position: p.position, division_agency: p.division_agency,
        phone: p.phone, email: p.email, is_primary: p.is_primary, special_requirements: p.special_requirements
      }));
    @else
      const currentUser = @json(auth()->user());
      if (currentUser) {
        // Ensure current user has all required fields
        const userWithDefaults = {
          ...currentUser,
          employee_name: currentUser.name || currentUser.employee_name || 'Unknown',
          position: currentUser.position || 'Staff',
          division_agency: currentUser.division_agency || currentUser.department || 'Not Specified',
          email: currentUser.email || '',
          phone: currentUser.phone || ''
        };
        console.log('Adding current user as participant:', userWithDefaults);
        addParticipant(userWithDefaults);
      }
    @endif

    // Initialize UI bindings
    updateParticipantsDisplay();
    setupSearch();
    setupSelectAll();
    
    // Force creation of hidden inputs for existing participants
    if (selectedParticipants.length > 0) {
      console.log('Initial participants found:', selectedParticipants.length);
      // Force update to ensure hidden inputs are created
      setTimeout(() => {
        updateParticipantsDisplay();
        // Double-check after update
        const inputCount = document.querySelectorAll('input[name^="participants["]').length;
        console.log('Hidden inputs created:', inputCount);
        if (inputCount === 0) {
          console.error('Failed to create hidden inputs! Retrying...');
          updateParticipantsDisplay();
        }
      }, 100);
    }
    
    // Add another failsafe after full page load
    window.addEventListener('load', function() {
      setTimeout(() => {
        if (selectedParticipants.length > 0) {
          const inputs = document.querySelectorAll('input[name^="participants["]');
          if (inputs.length === 0) {
            console.warn('No participant inputs found on page load, creating them now...');
            updateParticipantsDisplay();
          }
        }
      }, 500);
    });
    
  // Initialize workflow UI
    initializeWorkflowUI();
    
    // Ensure participants are maintained when switching workflow modes
    preserveParticipantsOnModeSwitch();
    
    // Initialize template selection handler
    initializeTemplateSelection();
  });
  
  // === ENHANCED WORKFLOW FUNCTIONALITY ===
  
  let workflowSteps = [];
  let workflowMode = 'custom'; // 'template' or 'custom'
  let stepCounter = 1;
  
  function toggleWorkflowMode(mode) {
    workflowMode = mode;
    const templateBtn = document.getElementById('useTemplateBtn');
    const customBtn = document.getElementById('customWorkflowBtn');
    const templateDiv = document.getElementById('template-workflow');
    const customDiv = document.getElementById('custom-workflow');
    
    console.log('Switching workflow mode to:', mode);
    console.log('Template div found:', !!templateDiv);
    console.log('Custom div found:', !!customDiv);
    
    if (mode === 'template') {
      if (templateBtn) {
        templateBtn.classList.remove('btn-outline-primary');
        templateBtn.classList.add('btn-primary');
      }
      if (customBtn) {
        customBtn.classList.remove('btn-primary');
        customBtn.classList.add('btn-outline-primary');
      }
      if (templateDiv) {
        templateDiv.style.display = 'block';
        console.log('Template div is now visible');
      }
      if (customDiv) {
        customDiv.style.display = 'none';
      }
    } else {
      if (customBtn) {
        customBtn.classList.remove('btn-outline-primary');
        customBtn.classList.add('btn-primary');
      }
      if (templateBtn) {
        templateBtn.classList.remove('btn-primary');
        templateBtn.classList.add('btn-outline-primary');
      }
      if (customDiv) {
        customDiv.style.display = 'block';
      }
      if (templateDiv) {
        templateDiv.style.display = 'none';
      }
    }
  }
  
  function initializeWorkflowUI() {
    // Check if templates are available and default to template mode if they are
    const hasTemplates = document.querySelectorAll('#workflow_template_id option[value]:not([value=""])').length > 0;
    const defaultMode = hasTemplates ? 'template' : 'custom';
    
    console.log('Initializing workflow UI - Templates available:', hasTemplates);
    console.log('Default mode:', defaultMode);
    
    // Set default mode based on template availability
    toggleWorkflowMode(defaultMode);
    
    // Add event listener for adding workflow steps only if custom workflow container exists
    const addStepBtn = document.getElementById('add-workflow-step');
    if (addStepBtn) {
      addStepBtn.addEventListener('click', addWorkflowStep);
      
      // Only add default step if in custom mode
      if (defaultMode === 'custom') {
        addWorkflowStep();
      }
    }
  }
  
  function addWorkflowStep(stepData = null) {
    const container = document.getElementById('workflow-steps-container');
    const stepId = stepData?.id || `step_${stepCounter++}`;
    
    const stepHtml = `
      <div class="workflow-step mb-4 p-3 border rounded" id="${stepId}" data-step-id="${stepId}">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="mb-0 text-primary">
            <i class="fas fa-layer-group mr-2"></i>
            Step ${workflowSteps.length + 1}
          </h6>
          <div>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveStepUp('${stepId}')" title="Move Up">
              <i class="fas fa-arrow-up"></i>
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="moveStepDown('${stepId}')" title="Move Down">
              <i class="fas fa-arrow-down"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeWorkflowStep('${stepId}')" title="Remove Step">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Step Name</label>
              <input type="text" class="form-control" name="workflow[${stepId}][step_name]" 
                     value="${stepData?.step_name || 'Approval Step ' + (workflowSteps.length + 1)}" 
                     placeholder="e.g., Regional Director Approval">
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label class="form-label">Step Type</label>
              <select class="form-control" name="workflow[${stepId}][step_type]" onchange="toggleCompletionRule('${stepId}', this.value)">
                <option value="sequential" ${stepData?.step_type === 'sequential' ? 'selected' : ''}>Sequential</option>
                <option value="parallel" ${stepData?.step_type === 'parallel' ? 'selected' : ''}>Parallel</option>
              </select>
            </div>
          </div>
        </div>
        
        <div class="completion-rule-section" id="completion-rule-${stepId}" style="${stepData?.step_type === 'parallel' ? '' : 'display: none;'}">
          <div class="row">
            <div class="col-md-6">
              <div class="mb-3">
                <label class="form-label">Completion Rule</label>
                <select class="form-control" name="workflow[${stepId}][completion_rule]" onchange="toggleCustomApprovals('${stepId}', this.value)">
                  <option value="all" ${stepData?.completion_rule === 'all' ? 'selected' : ''}>All Must Approve</option>
                  <option value="majority" ${stepData?.completion_rule === 'majority' ? 'selected' : ''}>Majority Must Approve</option>
                  <option value="any" ${stepData?.completion_rule === 'any' ? 'selected' : ''}>Any One Can Approve</option>
                  <option value="custom" ${stepData?.completion_rule === 'custom' ? 'selected' : ''}>Custom Number</option>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="mb-3 custom-approvals" id="custom-approvals-${stepId}" style="${stepData?.completion_rule === 'custom' ? '' : 'display: none;'}">
                <label class="form-label">Required Approvals</label>
                <input type="number" class="form-control" name="workflow[${stepId}][required_approvals]" 
                       value="${stepData?.required_approvals || 1}" min="1" placeholder="Number of required approvals">
              </div>
            </div>
          </div>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Select Approvers</label>
          <div class="approvers-section" id="approvers-${stepId}">
            <div class="d-flex gap-2 mb-2">
              <button type="button" class="btn btn-sm btn-primary" onclick="showApproverSelector('${stepId}')">
                <i class="fas fa-user-plus mr-1"></i> Add Approver
              </button>
            </div>
            <div class="selected-approvers" id="selected-approvers-${stepId}">
              <!-- Selected approvers will be listed here -->
            </div>
          </div>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Description (Optional)</label>
          <textarea class="form-control" name="workflow[${stepId}][description]" rows="2" 
                    placeholder="Describe the purpose of this approval step">${stepData?.description || ''}</textarea>
        </div>
      </div>
    `;
    
    container.insertAdjacentHTML('beforeend', stepHtml);
    workflowSteps.push({ id: stepId, approvers: [] });
    updateStepNumbers();
  }
  
  function toggleCompletionRule(stepId, stepType) {
    const ruleSection = document.getElementById(`completion-rule-${stepId}`);
    if (stepType === 'parallel') {
      ruleSection.style.display = 'block';
    } else {
      ruleSection.style.display = 'none';
    }
  }
  
  function toggleCustomApprovals(stepId, rule) {
    const customSection = document.getElementById(`custom-approvals-${stepId}`);
    if (rule === 'custom') {
      customSection.style.display = 'block';
    } else {
      customSection.style.display = 'none';
    }
  }
  
  function showApproverSelector(stepId) {
    // Show the user selector panel (reuse existing functionality)
    currentStepId = stepId; // Store current step ID
    showUserSelector();
    
    // Modify the "Add Selected" button to add to workflow step instead of participants
    const addButton = document.querySelector('#userSelectorPanel .btn-primary');
    addButton.onclick = function() { addSelectedUsersToWorkflowStep(stepId); };
  }
  
  function addSelectedUsersToWorkflowStep(stepId) {
    const checkboxes = document.querySelectorAll('#userSelectorPanel .user-checkbox:checked');
    const stepObj = workflowSteps.find(s => s.id === stepId);
    
    checkboxes.forEach(checkbox => {
      const userData = JSON.parse(checkbox.dataset.user);
      
      // Check if user is already added to this step
      if (!stepObj.approvers.find(a => a.id === userData.id)) {
        stepObj.approvers.push({
          id: userData.id,
          name: userData.name,
          title: userData.position || 'Staff',
          email: userData.email
        });
      }
    });
    
    updateStepApproversDisplay(stepId);
    hideUserSelector();
  }
  
  function updateStepApproversDisplay(stepId) {
    const container = document.getElementById(`selected-approvers-${stepId}`);
    const stepObj = workflowSteps.find(s => s.id === stepId);
    
    if (!stepObj || stepObj.approvers.length === 0) {
      container.innerHTML = '<div class="text-muted">No approvers selected</div>';
      return;
    }
    
    let html = '';
    stepObj.approvers.forEach((approver, index) => {
      html += `
        <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded mb-1">
          <div class="d-flex align-items-center">
            <i class="fas fa-user-circle fa-lg text-primary mr-2"></i>
            <div>
              <strong>${approver.name}</strong>
              <br><small class="text-muted">${approver.title} • ${approver.email}</small>
            </div>
          </div>
          <div>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeStepApprover('${stepId}', ${approver.id})" title="Remove">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <input type="hidden" name="workflow[${stepId}][approvers][${index}][user_id]" value="${approver.id}">
          <input type="hidden" name="workflow[${stepId}][approvers][${index}][name]" value="${approver.name}">
          <input type="hidden" name="workflow[${stepId}][approvers][${index}][title]" value="${approver.title}">
        </div>
      `;
    });
    
    container.innerHTML = html;
  }
  
  function removeStepApprover(stepId, approverId) {
    const stepObj = workflowSteps.find(s => s.id === stepId);
    stepObj.approvers = stepObj.approvers.filter(a => a.id !== approverId);
    updateStepApproversDisplay(stepId);
  }
  
  function removeWorkflowStep(stepId) {
    if (workflowSteps.length <= 1) {
      alert('At least one approval step is required.');
      return;
    }
    
    document.getElementById(stepId).remove();
    workflowSteps = workflowSteps.filter(s => s.id !== stepId);
    updateStepNumbers();
  }
  
  function moveStepUp(stepId) {
    const stepElement = document.getElementById(stepId);
    const prevElement = stepElement.previousElementSibling;
    if (prevElement) {
      stepElement.parentNode.insertBefore(stepElement, prevElement);
      updateStepNumbers();
    }
  }
  
  function moveStepDown(stepId) {
    const stepElement = document.getElementById(stepId);
    const nextElement = stepElement.nextElementSibling;
    if (nextElement) {
      stepElement.parentNode.insertBefore(nextElement, stepElement);
      updateStepNumbers();
    }
  }
  
  function updateStepNumbers() {
    const stepElements = document.querySelectorAll('.workflow-step');
    stepElements.forEach((element, index) => {
      const stepHeader = element.querySelector('h6');
      stepHeader.innerHTML = `<i class="fas fa-layer-group mr-2"></i>Step ${index + 1}`;
    });
  }
  
  // Fix for participants preservation
  function preserveParticipantsOnModeSwitch() {
    // Override the toggleWorkflowMode to preserve participants
    const originalToggleWorkflowMode = window.toggleWorkflowMode;
    window.toggleWorkflowMode = function(mode) {
      // Ensure participants display is updated before mode switch
      updateParticipantsDisplay();
      // Call original function
      originalToggleWorkflowMode(mode);
    };
    
    // Add form submission handler to ensure participants data is included
    const form = document.querySelector('form');
    if (form) {
      form.addEventListener('submit', function(e) {
        // Ensure participants display is updated before submission
        updateParticipantsDisplay();
        
        // Check if we have participants
        const participantInputs = document.querySelectorAll('input[name^="participants["]');
        if (participantInputs.length === 0 && selectedParticipants.length > 0) {
          // Force update participants display if hidden inputs are missing
          updateParticipantsDisplay();
        }
        
        // Debug: Log current state
        console.log('Form submission - selectedParticipants:', selectedParticipants);
        console.log('Form submission - participantInputs found:', participantInputs.length);
        console.log('Form submission - workflow mode:', workflowMode);
        
        // If in template mode, ensure a template is selected or null
        if (workflowMode === 'template') {
          const templateSelect = document.getElementById('workflow_template_id');
          if (templateSelect) {
            const selectedValue = templateSelect.value;
            console.log('Form submission - template selected:', selectedValue);
            
            // If empty string selected, ensure it's properly handled
            if (selectedValue === '') {
              // This is fine - it means use default workflow
              console.log('Using default workflow (no template selected)');
            }
          }
        } else {
          // In custom mode, ensure template field is cleared
          const templateSelect = document.getElementById('workflow_template_id');
          if (templateSelect) {
            templateSelect.value = '';
            console.log('Custom workflow mode - cleared template selection');
          }
        }
        
        // Validate we have at least one participant
        if (selectedParticipants.length === 0) {
          e.preventDefault();
          alert('Please add at least one travel participant.');
          return false;
        }
        
        // Final check: ensure hidden inputs are present
        const finalCheck = document.querySelectorAll('input[name^="participants["]');
        console.log('Final submission check - participant inputs found:', finalCheck.length);
        if (finalCheck.length === 0 && selectedParticipants.length > 0) {
          console.error('Critical: No participant inputs found despite having participants!');
          // Try one last time to create them
          updateParticipantsDisplay();
          
          // Check again
          const secondCheck = document.querySelectorAll('input[name^="participants["]');
          if (secondCheck.length === 0) {
            e.preventDefault();
            alert('Error: Unable to submit participant data. Please refresh the page and try again.');
            return false;
          }
        }
      });
    }
  }
  
  // No need for duplicate function - the original updateParticipantsDisplay handles everything
  
  // Expose functions to global scope for inline event handlers
  window.toggleWorkflowMode = toggleWorkflowMode;
  window.addWorkflowStep = addWorkflowStep;
  window.removeWorkflowStep = removeWorkflowStep;
  window.moveStepUp = moveStepUp;
  window.moveStepDown = moveStepDown;
  window.toggleCompletionRule = toggleCompletionRule;
  window.toggleCustomApprovals = toggleCustomApprovals;
  window.showApproverSelector = showApproverSelector;
  window.removeStepApprover = removeStepApprover;
  
  // Template selection functionality
  function initializeTemplateSelection() {
    const templateSelect = document.getElementById('workflow_template_id');
    const templatePreview = document.getElementById('template-preview');
    const templateStepsPreview = document.getElementById('template-steps-preview');
    
    if (templateSelect) {
      templateSelect.addEventListener('change', function() {
        const selectedValue = this.value;
        
        if (selectedValue) {
          // Show loading
          if (templateStepsPreview) {
            templateStepsPreview.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin mr-2"></i>Loading template preview...</div>';
          }
          if (templatePreview) {
            templatePreview.style.display = 'block';
          }
          
          // Load template preview via AJAX
          const selectedOption = this.options[this.selectedIndex];
          const templateInfo = selectedOption.textContent;
          
          // Make AJAX call to get template details
          fetch(`/api/workflow-templates/${selectedValue}`)
            .then(response => response.json())
            .then(data => {
              if (templateStepsPreview && data.steps) {
                let stepsHtml = `
                  <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-check-circle text-success mr-2"></i>
                    <strong>Template Selected:</strong> ${templateInfo}
                  </div>
                  <div class="mb-2">
                    <strong>Approval Steps:</strong>
                  </div>
                  <ol class="list-unstyled">
                `;
                
                // Group steps by sequence for display
                const stepsBySequence = {};
                data.steps.forEach(step => {
                  if (!stepsBySequence[step.sequence]) {
                    stepsBySequence[step.sequence] = [];
                  }
                  stepsBySequence[step.sequence].push(step);
                });
                
                Object.keys(stepsBySequence).sort((a, b) => parseInt(a) - parseInt(b)).forEach(sequence => {
                  const stepsInSeq = stepsBySequence[sequence];
                  stepsHtml += `<li class="mb-2">`;
                  
                  if (stepsInSeq.length > 1) {
                    stepsHtml += `<strong>Step ${sequence} (Parallel):</strong><ul>`;
                    stepsInSeq.forEach(step => {
                      stepsHtml += `<li><i class="fas fa-user text-primary mr-1"></i>${step.approver_name} (${step.approver_title || 'Staff'})</li>`;
                    });
                    stepsHtml += `</ul>`;
                  } else {
                    const step = stepsInSeq[0];
                    stepsHtml += `<strong>Step ${sequence}:</strong> <i class="fas fa-user text-primary mr-1"></i>${step.approver_name} (${step.approver_title || 'Staff'})`;
                  }
                  
                  stepsHtml += `</li>`;
                });
                
                stepsHtml += `
                  </ol>
                  <div class="text-muted small mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Total Steps: ${Object.keys(stepsBySequence).length}, 
                    Total Approvers: ${data.steps.length}
                  </div>
                `;
                
                templateStepsPreview.innerHTML = stepsHtml;
              }
            })
            .catch(error => {
              console.error('Failed to load template details:', error);
              if (templateStepsPreview) {
                templateStepsPreview.innerHTML = `
                  <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-check-circle text-success mr-2"></i>
                    <strong>Template Selected:</strong> ${templateInfo}
                  </div>
                  <div class="text-muted small">
                    <i class="fas fa-info-circle mr-1"></i>
                    This template will be used for the approval workflow.
                  </div>
                `;
              }
            });
        } else {
          // Hide preview for default workflow
          if (templatePreview) {
            templatePreview.style.display = 'none';
          }
        }
      });
    }
  }
  
  window.initializeTemplateSelection = initializeTemplateSelection;
  
  // Debug function to check participant data
  window.debugParticipants = function() {
    console.log('=== PARTICIPANT DEBUG INFO ===');
    console.log('Selected Participants:', selectedParticipants);
    console.log('Participant Count:', selectedParticipants.length);
    
    const hiddenInputs = document.querySelectorAll('input[name^="participants["]');
    console.log('Hidden Input Count:', hiddenInputs.length);
    
    hiddenInputs.forEach((input, index) => {
      console.log(`Input ${index}: ${input.name} = ${input.value}`);
    });
    
    if (hiddenInputs.length === 0) {
      console.error('NO HIDDEN INPUTS FOUND! Trying to create them now...');
      updateParticipantsDisplay();
      
      const afterUpdate = document.querySelectorAll('input[name^="participants["]');
      console.log('After update, hidden inputs:', afterUpdate.length);
    }
    
    // Check if container exists
    const container = document.getElementById('participants-hidden-inputs');
    console.log('Hidden container exists:', !!container);
    if (container) {
      console.log('Container HTML:', container.innerHTML.substring(0, 500));
    }
  };
</script>
@endpush

<!-- Inline User Selection Panel (no modal) - Moved to Top -->
<div id="userSelectorPanel" class="card mb-4 d-none">
  <div class="card-header">
    <h5 class="mb-0 text-primary">
      <i class="fas fa-users me-2"></i>
      Select Travel Participants
    </h5>
  </div>
  <div class="card-body">
    <div class="row mb-3">
      <div class="col-md-8">
        <input type="text" class="form-control" id="userSearchInline" placeholder="Search users by name, email, or position...">
      </div>
      <div class="col-md-4">
        <select class="form-control" id="departmentFilterInline">
          <option value="">All Departments</option>
        </select>
      </div>
    </div>

    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
      <table class="table table-sm table-hover">
        <thead class="table-light sticky-top">
          <tr>
            <th width="5%"><input type="checkbox" id="selectAllInline"></th>
            <th width="25%">Name</th>
            <th width="20%">Position</th>
            <th width="25%">Division/Agency</th>
            <th width="25%">Email</th>
          </tr>
        </thead>
        <tbody id="usersTableBodyInline"></tbody>
      </table>
    </div>

    <!-- Pagination controls -->
    <div class="d-flex justify-content-between align-items-center mt-2">
      <div>
        <label for="pageSizeInline" class="me-2">Rows per page</label>
        <select id="pageSizeInline" class="form-select form-select-sm d-inline-block" style="width:auto;">
          <option value="10" selected>10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
      </div>
      <div class="d-flex align-items-center gap-2">
        <small id="pageInfoInline">Page 1 of 1</small>
        <button id="prevPageInline" type="button" class="btn btn-outline-secondary btn-sm">Prev</button>
        <button id="nextPageInline" type="button" class="btn btn-outline-secondary btn-sm">Next</button>
      </div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-end">
    <button type="button" class="btn btn-outline-secondary me-2" onclick="hideUserSelector()">Close</button>
    <button type="button" class="btn btn-primary" onclick="addSelectedUsers()">Add Selected</button>
  </div>
</div>

<!-- Hidden inputs container for participants - MUST be inside the form -->
<div id="participants-hidden-inputs" style="display: none;">
  <!-- Dynamic participant inputs will be added here by JavaScript -->
</div>

<div class="row">
  <!-- Participants Section -->
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0 text-primary">
        <i class="fas fa-users mr-2"></i>
        Travel Participants
      </h5>
      <button type="button" class="btn btn-success btn-sm" onclick="showUserSelector()">
        <i class="fas fa-plus mr-1"></i>
        Add Participant
      </button>
    </div>
  </div>

  <div class="col-md-12">
    <!-- Selected Participants Table -->
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="fas fa-list mr-2"></i>
          Selected Participants
        </h6>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0" id="participants-table">
            <thead class="table-light">
              <tr>
                <th width="5%">#</th>
                <th width="25%">Name</th>
                <th width="20%">Position</th>
                <th width="20%">Division/Agency</th>
                <th width="15%">Contact</th>
                <th width="10%">Role</th>
                <th width="5%">Action</th>
              </tr>
            </thead>
            <tbody id="participants-tbody">
              <!-- Participants will be added here -->
            </tbody>
          </table>
        </div>
        <div class="p-3 text-center" id="no-participants" style="display: none;">
          <i class="fas fa-users-slash fa-2x text-muted mb-2"></i>
          <p class="text-muted mb-0">No participants selected. Click "Add Participant" to select users.</p>
        </div>
      </div>
    </div>
    
    <div class="alert alert-info mt-3">
      <i class="fas fa-info-circle mr-2"></i>
      <strong>Note:</strong> The first participant will be marked as the primary traveler.
      @if(config('app.debug'))
        <button type="button" class="btn btn-sm btn-warning float-right" onclick="debugParticipants()">
          <i class="fas fa-bug"></i> Debug
        </button>
        <button type="button" class="btn btn-sm btn-success float-right mr-2" onclick="fixParticipants()">
          <i class="fas fa-wrench"></i> Fix Participants
        </button>
      @endif
    </div>
    
    <script>
      // Quick fix function
      function fixParticipants() {
        if (selectedParticipants.length === 0) {
          alert('No participants to fix. Please add a participant first.');
          return;
        }
        
        // Force update with complete data
        selectedParticipants = selectedParticipants.map(p => ({
          ...p,
          employee_name: p.employee_name || p.name || 'Unknown',
          position: p.position || 'Staff',
          division_agency: p.division_agency || 'REGIONAL OFFICE',
          email: p.email || '',
          phone: p.phone || '',
          employee_id: p.employee_id || '',
          is_primary: p.is_primary || false
        }));
        
        // Force recreation of hidden inputs
        updateParticipantsDisplay();
        
        // Verify
        setTimeout(() => {
          const inputs = document.querySelectorAll('input[name^="participants["]');
          alert(`Fixed! Created ${inputs.length} hidden inputs for ${selectedParticipants.length} participant(s).`);
          console.log('Fixed participants:', selectedParticipants);
        }, 100);
      }
    </script>
  </div>

  <!-- Enhanced Approval Workflow Section -->
  <div class="col-12 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 class="mb-0 text-primary">
        <i class="fas fa-route mr-2"></i>
        Approval Workflow
      </h5>
      <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-primary btn-sm" id="useTemplateBtn" onclick="toggleWorkflowMode('template')">
          <i class="fas fa-template mr-1"></i> Use Template
        </button>
        <button type="button" class="btn btn-primary btn-sm" id="customWorkflowBtn" onclick="toggleWorkflowMode('custom')">
          <i class="fas fa-user-plus mr-1"></i> Custom Workflow
        </button>
      </div>
    </div>
  </div>

  <!-- Template-based Workflow -->
  <div class="col-md-12" id="template-workflow" style="display: none;">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="fas fa-template mr-2"></i>
          Workflow Template Selection
        </h6>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label for="workflow_template_id" class="form-label">Select Workflow Template</label>
          <select name="workflow_template_id" 
                  id="workflow_template_id" 
                  class="form-control @error('workflow_template_id') is-invalid @enderror">
            <option value="">Use Default Workflow</option>
            @if(isset($availableTemplates) && $availableTemplates->count() > 0)
              @foreach($availableTemplates as $template)
                <option value="{{ $template->id }}" 
                        {{ old('workflow_template_id', $isEdit ? $travelOrder->workflow_template_id : '') == $template->id ? 'selected' : '' }}
                        @if($template->is_default) data-default="true" @endif>
                  {{ $template->name }}
                  @if($template->is_default) (Default) @endif
                  - {{ $template->steps->count() }} steps
                </option>
              @endforeach
            @else
              <option value="" disabled>No workflow templates available</option>
            @endif
          </select>
          @error('workflow_template_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
          <div class="form-text mt-2">
            <i class="fas fa-info-circle mr-1"></i>
            @if(isset($availableTemplates) && $availableTemplates->count() > 0)
              Select a pre-defined workflow template or use the default workflow.
              <a href="{{ route('workflows.create') }}" target="_blank" class="text-primary">Create new template</a>
            @else
              <span class="text-warning">No workflow templates found.</span> 
              <a href="{{ route('workflows.create') }}" target="_blank" class="btn btn-sm btn-primary ml-2">
                <i class="fas fa-plus mr-1"></i>Create Your First Template
              </a>
            @endif
          </div>
        </div>
        
        <!-- Template Preview (will be shown when a template is selected) -->
        <div id="template-preview" style="display: none;">
          <h6 class="text-primary mb-2">Template Preview:</h6>
          <div id="template-steps-preview" class="border rounded p-3 bg-light">
            <!-- Template steps will be loaded here -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Custom Specific Person Workflow -->
  <div class="col-md-12" id="custom-workflow">
    <div class="card">
      <div class="card-header">
        <h6 class="mb-0">
          <i class="fas fa-users-cog mr-2"></i>
          Custom Approval Steps
        </h6>
      </div>
      <div class="card-body">
        <div id="workflow-steps-container">
          <!-- Dynamic workflow steps will be added here -->
        </div>
        
        <div class="text-center mt-3">
          <button type="button" class="btn btn-success" id="add-workflow-step">
            <i class="fas fa-plus mr-1"></i>
            Add Approval Step
          </button>
        </div>
        
        <div class="alert alert-info mt-3">
          <i class="fas fa-info-circle mr-2"></i>
          <strong>Workflow Types:</strong><br>
          • <strong>Sequential:</strong> Approvers act one after another<br>
          • <strong>Parallel:</strong> Multiple approvers act simultaneously<br>
          • <strong>Completion Rules:</strong> All, Majority, Any, or Custom number required
        </div>
      </div>
    </div>
  </div>

  <!-- Travel Details Section -->
  <div class="col-12 mt-3">
    <h5 class="mb-3 text-primary">Travel Details</h5>
  </div>

  <div class="col-md-6">
    <div class="mb-3">
      <label for="date_of_travel_from" class="form-label">Travel Date From <span class="text-danger">*</span></label>
      <input type="date" 
             name="date_of_travel_from" 
             id="date_of_travel_from" 
             class="form-control @error('date_of_travel_from') is-invalid @enderror"
             value="{{ old('date_of_travel_from', $isEdit ? $travelOrder->date_of_travel_from->format('Y-m-d') : '') }}"
             required>
      @error('date_of_travel_from')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="mb-3">
      <label for="date_of_travel_to" class="form-label">Travel Date To <span class="text-danger">*</span></label>
      <input type="date" 
             name="date_of_travel_to" 
             id="date_of_travel_to" 
             class="form-control @error('date_of_travel_to') is-invalid @enderror"
             value="{{ old('date_of_travel_to', $isEdit ? $travelOrder->date_of_travel_to->format('Y-m-d') : '') }}"
             required>
      @error('date_of_travel_to')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="mb-3">
      <label for="source_of_fund" class="form-label">Source of Fund <span class="text-danger">*</span></label>
      <select name="source_of_fund" 
              id="source_of_fund" 
              class="form-control @error('source_of_fund') is-invalid @enderror"
              required>
        <option value="">Select Source of Fund</option>
        <option value="Cybersecurity" {{ old('source_of_fund', $isEdit ? $travelOrder->source_of_fund : '') == 'Cybersecurity' ? 'selected' : '' }}>Cybersecurity</option>
        <option value="Budget" {{ old('source_of_fund', $isEdit ? $travelOrder->source_of_fund : '') == 'Budget' ? 'selected' : '' }}>Budget</option>
        <option value="Personal" {{ old('source_of_fund', $isEdit ? $travelOrder->source_of_fund : '') == 'Personal' ? 'selected' : '' }}>Personal</option>
        <option value="Other" {{ old('source_of_fund', $isEdit ? $travelOrder->source_of_fund : '') == 'Other' ? 'selected' : '' }}>Other</option>
      </select>
      @error('source_of_fund')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-6">
    <div class="mb-3">
      <label for="official_vehicle" class="form-label">Official Vehicle</label>
      <input type="text" 
             name="official_vehicle" 
             id="official_vehicle" 
             class="form-control @error('official_vehicle') is-invalid @enderror"
             value="{{ old('official_vehicle', $isEdit ? $travelOrder->official_vehicle : '') }}"
             placeholder="e.g., Service Vehicle, Private Vehicle, Rental">
      @error('official_vehicle')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-12">
    <div class="mb-3">
      <label for="purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
      <textarea name="purpose" 
                id="purpose" 
                rows="3" 
                class="form-control @error('purpose') is-invalid @enderror"
                required>{{ old('purpose', $isEdit ? $travelOrder->purpose : '') }}</textarea>
      @error('purpose')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-12">
    <div class="mb-3">
      <label for="destination" class="form-label">Destination <span class="text-danger">*</span></label>
      <textarea name="destination" 
                id="destination" 
                rows="3" 
                class="form-control @error('destination') is-invalid @enderror"
                placeholder="Describe the full travel destination"
                required>{{ old('destination', $isEdit ? $travelOrder->destination : '') }}</textarea>
      @error('destination')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-8">
    <div class="mb-3">
      <label for="farthest_destination" class="form-label">Farthest Destination <span class="text-danger">*</span></label>
      <input type="text" 
             name="farthest_destination" 
             id="farthest_destination" 
             class="form-control @error('farthest_destination') is-invalid @enderror"
             value="{{ old('farthest_destination', $isEdit ? $travelOrder->farthest_destination : '') }}"
             placeholder="e.g., Bayombong, Nueva Vizcaya"
             required>
      @error('farthest_destination')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>

  <div class="col-md-4">
    <div class="mb-3">
      <label for="approx_distance" class="form-label">Approx Distance (km) <span class="text-danger">*</span></label>
      <input type="number" 
             name="approx_distance" 
             id="approx_distance" 
             step="0.01" 
             min="0" 
             max="9999.99"
             class="form-control @error('approx_distance') is-invalid @enderror"
             value="{{ old('approx_distance', $isEdit ? $travelOrder->approx_distance : '') }}"
             required>
      @error('approx_distance')
        <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
  </div>
</div>
