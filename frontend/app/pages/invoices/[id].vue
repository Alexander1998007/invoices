<template>
  <div class="p-6 max-w-3xl mx-auto">
    <button
        @click="navigateTo('/invoices')"
        class="mb-4 text-sm text-blue-600 hover:underline flex items-center gap-1"
    >
      ← Back to Invoices
    </button>

    <div v-if="pending" class="text-center py-12 text-gray-500">Loading invoice details...</div>
    <div v-else-if="error || !invoice" class="bg-red-50 text-red-600 p-4 rounded-md">
      Invoice not found or error loading data.
    </div>

    <div v-else class="bg-white shadow-sm border rounded-lg p-6 space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-start border-b pb-4">
        <div>
          <h1 class="text-2xl font-bold text-gray-800">Invoice {{ invoice.number }}</h1>
          <p class="text-sm text-gray-500">Created at: {{ formatDate(invoice.created_at) }}</p>
        </div>
        <span :class="statusBadge(invoice.status)" class="px-3 py-1 rounded-full text-xs font-bold uppercase">
          {{ invoice.status }}
        </span>
      </div>

      <!-- Info Card -->
      <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-md text-sm">
        <div><span class="text-gray-500">Supplier:</span> <strong>{{ invoice.supplier_name }}</strong></div>
        <div><span class="text-gray-500">Tax ID:</span> <strong>{{ invoice.supplier_tax_id }}</strong></div>
        <div><span class="text-gray-500">Issue Date:</span> <strong>{{ formatDate(invoice.issue_date) }}</strong></div>
        <div><span class="text-gray-500">Last Updated:</span> <strong>{{ formatDate(invoice.updated_at) }}</strong></div>
      </div>

      <!-- Notice if locked -->
      <div v-if="invoice.status !== 'pending'" class="bg-amber-50 border border-amber-200 text-amber-800 p-3 rounded text-sm">
        Notice: Only invoices with <strong>pending</strong> status can be edited.
      </div>

      <!-- Edit Form -->
      <form @submit.prevent="submitForm" class="space-y-4">
        <fieldset :disabled="invoice.status !== 'pending'" class="space-y-4 disabled:opacity-60">
          <div>
            <label class="block text-sm font-medium text-gray-700">Net Amount</label>
            <input
                type="number"
                step="0.01"
                v-model.number="form.net_amount"
                class="w-full border p-2 rounded-md mt-1 focus:ring-2 focus:ring-blue-500 outline-none"
            />
            <span v-if="errors.net_amount" class="text-red-500 text-xs mt-1 block">{{ errors.net_amount }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">VAT Amount</label>
            <input
                type="number"
                step="0.01"
                v-model.number="form.vat_amount"
                class="w-full border p-2 rounded-md mt-1 focus:ring-2 focus:ring-blue-500 outline-none"
            />
            <span v-if="errors.vat_amount" class="text-red-500 text-xs mt-1 block">{{ errors.vat_amount }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Gross Amount (Auto-Calculated)</label>
            <input
                type="number"
                :value="computedGross"
                disabled
                class="w-full border p-2 rounded-md mt-1 bg-gray-100 font-semibold text-gray-700 cursor-not-allowed"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Due Date</label>
            <input
                type="date"
                v-model="form.due_date"
                class="w-full border p-2 rounded-md mt-1 focus:ring-2 focus:ring-blue-500 outline-none"
            />
            <span v-if="errors.due_date" class="text-red-500 text-xs mt-1 block">{{ errors.due_date }}</span>
          </div>
        </fieldset>

        <button
            v-if="invoice.status === 'pending'"
            type="submit"
            :disabled="isSubmitting"
            class="bg-blue-600 text-white px-5 py-2.5 rounded-md font-medium hover:bg-blue-700 disabled:opacity-50 transition"
        >
          {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, watch } from 'vue'
import { useRoute, navigateTo } from '#imports'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { toTypedSchema } from '@vee-validate/zod'

const route = useRoute()

const { data: invoice, pending, error, refresh } = await useApiFetch(`/invoices/${route.params.id}`)

const validationSchema = toTypedSchema(
    z.object({
      net_amount: z.number().positive('Net amount must be greater than 0'),
      vat_amount: z.number().min(0, 'VAT must be >= 0'),
      due_date: z.string().nullable().optional(),
    })
)

const { handleSubmit, errors, isSubmitting, setValues } = useForm({
  validationSchema,
})

const form = reactive({
  net_amount: 0,
  vat_amount: 0,
  due_date: '',
})

watch(invoice, (inv) => {
  if (inv) {
    form.net_amount = Number(inv.net_amount) || 0
    form.vat_amount = Number(inv.vat_amount) || 0
    form.due_date = inv.due_date ? inv.due_date.split('T')[0] : ''
    setValues(form)
  }
}, { immediate: true })

const computedGross = computed(() => {
  const net = Number(form.net_amount) || 0
  const vat = Number(form.vat_amount) || 0
  return (net + vat).toFixed(2)
})

const submitForm = handleSubmit(async (values) => {
  try {
    await $api(`/invoices/${route.params.id}`, {
      method: 'PUT',
      body: values,
    })
    await refresh()
  } catch (err) {
    alert('Failed to update invoice: ' + (err.data?.message || err.message))
  }
})

const statusBadge = (status) => ({
  'bg-amber-100 text-amber-800 border border-amber-200': status === 'pending',
  'bg-emerald-100 text-emerald-800 border border-emerald-200': status === 'approved',
  'bg-rose-100 text-rose-800 border border-rose-200': status === 'rejected',
})

const formatDate = (dateString) => {
  if (!dateString) return '—'
  const date = new Date(dateString)
  return isNaN(date.getTime()) ? '—' : date.toLocaleDateString()
}
</script>
