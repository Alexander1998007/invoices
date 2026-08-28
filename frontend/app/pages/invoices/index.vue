<template>
  <div class="p-6 max-w-6xl mx-auto space-y-6">
    <!-- Header with UI/UX Best Practices -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-5">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Invoices</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage and track your company billing documents</p>
      </div>

      <!-- Primary Action Button -->
      <button
          @click="navigateTo('/invoices/create')"
          class="inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium text-sm px-4 py-2.5 rounded-lg shadow-sm hover:shadow transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span>Create Invoice</span>
      </button>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-3 bg-gray-50 p-3 rounded-lg border border-gray-200/80">
      <div class="relative w-full sm:w-72">
        <input
            v-model="searchQuery"
            type="text"
            placeholder="Search number or supplier..."
            class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        />
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 absolute left-3 top-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>

      <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
        <select
            v-model="sortBy"
            class="border border-gray-300 px-3 py-2 rounded-md text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          <option value="created_at">Date Created</option>
          <option value="due_date">Due Date</option>
          <option value="gross_amount">Gross Amount</option>
          <option value="number">Invoice Number</option>
        </select>

        <button
            @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'"
            title="Toggle sort direction"
            class="border border-gray-300 px-3 py-2 rounded-md text-sm bg-white hover:bg-gray-100 font-mono text-gray-700"
        >
          {{ sortOrder.toUpperCase() }}
        </button>
      </div>
    </div>

    <!-- UI States -->
    <div v-if="pending" class="text-center py-16 text-gray-500 font-medium">
      Loading invoices...
    </div>

    <div v-else-if="error" class="bg-red-50 text-red-600 p-4 rounded-lg border border-red-200 text-sm">
      Failed to load invoices. Please check backend API connection.
    </div>

    <div v-else-if="!invoices?.length" class="text-center py-16 text-gray-500 bg-white rounded-lg border">
      No invoices found matching your criteria.
    </div>

    <!-- Data Table -->
    <div v-else class="space-y-4">
      <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
        <table class="w-full text-left border-collapse">
          <thead>
          <tr class="border-b bg-gray-50/70 text-xs font-semibold uppercase text-gray-500 tracking-wider">
            <th class="p-4">Number</th>
            <th class="p-4">Supplier</th>
            <th class="p-4">Gross Amount</th>
            <th class="p-4">Status</th>
            <th class="p-4">Due Date</th>
          </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-sm">
          <tr
              v-for="inv in paginatedInvoices"
              :key="inv.id"
              @click="navigateTo(`/invoices/${inv.id}`)"
              class="hover:bg-blue-50/40 cursor-pointer transition-colors"
          >
            <td class="p-4 font-semibold text-blue-600">{{ inv.number }}</td>
            <td class="p-4 text-gray-700 font-medium">{{ inv.supplier_name }}</td>
            <td class="p-4 font-semibold text-gray-900">{{ inv.gross_amount }} {{ inv.currency }}</td>
            <td class="p-4">
                <span :class="statusBadge(inv.status)" class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                  {{ inv.status }}
                </span>
            </td>
            <td class="p-4 text-gray-600">{{ formatDate(inv.due_date) }}</td>
          </tr>
          </tbody>
        </table>
      </div>

      <!-- Conditional Pagination (Only visible when items > 10) -->
      <div
          v-if="totalItems > itemsPerPage"
          class="flex flex-col sm:flex-row-reverse items-center justify-between gap-3 border-t border-gray-200 bg-white px-4 py-3 sm:px-6 rounded-lg border"
      >
        <!-- Кнопки пагінації (знизу на мобільних, праворуч на десктопі) -->
        <div class="flex items-center space-x-2">
          <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="px-3 py-1.5 border rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
          >
            Previous
          </button>

          <span class="text-sm text-gray-600 px-2 font-medium">
            Page {{ currentPage }} of {{ totalPages }}
          </span>

          <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="px-3 py-1.5 border rounded-md text-sm font-medium bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition"
          >
            Next
          </button>
        </div>

        <!-- Текст лічильника (зверху на мобільних, ліворуч на десктопі) -->
        <div class="text-sm text-gray-700 text-center sm:text-left">
          Showing <span class="font-medium">{{ pageStart }}</span> to <span class="font-medium">{{ pageEnd }}</span> of <span class="font-medium">{{ totalItems }}</span> invoices
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRuntimeConfig } from '#imports'

const config = useRuntimeConfig()

// Filter & Sort state
const searchQuery = ref('')
const sortBy = ref('created_at')
const sortOrder = ref('desc')

// Pagination state
const currentPage = ref(1)
const itemsPerPage = config.public.itemsPerPage

// API Data Fetching
const { data: invoices, pending, error } = await useApiFetch('/invoices', {
  query: {
    search: searchQuery,
    sort_by: sortBy,
    sort_order: sortOrder,
  },
})

// Reset pagination on filter change
watch([searchQuery, sortBy, sortOrder], () => {
  currentPage.value = 1
})

// Computed pagination properties
const totalItems = computed(() => invoices.value?.length || 0)
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage) || 1)
const pageStart = computed(() => ((currentPage.value - 1) * itemsPerPage) + 1)
const pageEnd = computed(() => Math.min(currentPage.value * itemsPerPage, totalItems.value))

const paginatedInvoices = computed(() => {
  if (!invoices.value) return []
  const start = (currentPage.value - 1) * itemsPerPage
  return invoices.value.slice(start, start + itemsPerPage)
})

// UI Helpers
const statusBadge = (status) => ({
  'bg-amber-100 text-amber-800 border border-amber-200/60': status === 'pending',
  'bg-emerald-100 text-emerald-800 border border-emerald-200/60': status === 'approved',
  'bg-rose-100 text-rose-800 border border-rose-200/60': status === 'rejected',
})

const formatDate = (dateString) => {
  if (!dateString) return '—'
  const date = new Date(dateString)
  return isNaN(date.getTime()) ? '—' : date.toLocaleDateString()
}
</script>
