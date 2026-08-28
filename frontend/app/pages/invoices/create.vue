<template>
  <div class="p-6 max-w-2xl mx-auto">
    <button
        @click="navigateTo('/invoices')"
        class="mb-4 text-sm text-blue-600 hover:underline flex items-center gap-1"
    >
      ← Back to Invoices
    </button>

    <div class="bg-white shadow-sm border rounded-lg p-6 space-y-6">
      <h1 class="text-2xl font-bold text-gray-800">Create New Invoice</h1>

      <form @submit.prevent="onSubmit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Invoice Number</label>
          <input
              type="text"
              v-model="number"
              v-bind="numberProps"
              placeholder="e.g. INV-2026-005"
              class="w-full border p-2 rounded-md mt-1 focus:ring-2 focus:ring-blue-500 outline-none"
          />
          <span v-if="errors.number" class="text-red-500 text-xs mt-1 block">{{ errors.number }}</span>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Supplier Name</label>
            <input
                type="text"
                v-model="supplier_name"
                v-bind="supplierNameProps"
                placeholder="Company Name"
                class="w-full border p-2 rounded-md mt-1 focus:ring-2 focus:ring-blue-500 outline-none"
            />
            <span v-if="errors.supplier_name" class="text-red-500 text-xs mt-1 block">{{ errors.supplier_name }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Supplier Tax ID</label>
            <input
                type="text"
                v-model="supplier_tax_id"
                v-bind="supplierTaxIdProps"
                placeholder="Tax ID / Code"
                class="w-full border p-2 rounded-md mt-1 focus:ring-2 focus:ring-blue-500 outline-none"
            />
            <span v-if="errors.supplier_tax_id" class="text-red-500 text-xs mt-1 block">{{ errors.supplier_tax_id }}</span>
          </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Net Amount</label>
            <input
                type="number"
                step="0.01"
                v-model.number="net_amount"
                v-bind="netAmountProps"
                class="w-full border p-2 rounded-md mt-1 focus:ring-2 focus:ring-blue-500 outline-none"
            />
            <span v-if="errors.net_amount" class="text-red-500 text-xs mt-1 block">{{ errors.net_amount }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">VAT Amount</label>
            <input
                type="number"
                step="0.01"
                v-model.number="vat_amount"
                v-bind="vatAmountProps"
                class="w-full border p-2 rounded-md mt-1 focus:ring-2 focus:ring-blue-500 outline-none"
            />
            <span v-if="errors.vat_amount" class="text-red-500 text-xs mt-1 block">{{ errors.vat_amount }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Gross (Auto)</label>
            <input
                type="number"
                :value="computedGross"
                disabled
                class="w-full border p-2 rounded-md mt-1 bg-gray-100 font-semibold text-gray-700 cursor-not-allowed"
            />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Issue Date</label>
            <input
                type="date"
                v-model="issue_date"
                v-bind="issueDateProps"
                class="w-full border p-2 rounded-md mt-1 focus:ring-2 focus:ring-blue-500 outline-none"
            />
            <span v-if="errors.issue_date" class="text-red-500 text-xs mt-1 block">{{ errors.issue_date }}</span>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700">Due Date</label>
            <input
                type="date"
                v-model="due_date"
                v-bind="dueDateProps"
                class="w-full border p-2 rounded-md mt-1 focus:ring-2 focus:ring-blue-500 outline-none"
            />
            <span v-if="errors.due_date" class="text-red-500 text-xs mt-1 block">{{ errors.due_date }}</span>
          </div>
        </div>

        <button
            type="submit"
            :disabled="isSubmitting"
            class="w-full bg-blue-600 text-white py-2.5 rounded-md font-medium hover:bg-blue-700 disabled:opacity-50 transition"
        >
          {{ isSubmitting ? 'Creating...' : 'Create Invoice' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { navigateTo, useRuntimeConfig } from '#imports'
import { useForm } from 'vee-validate'
import { z } from 'zod'
import { toTypedSchema } from '@vee-validate/zod'

const config = useRuntimeConfig()
const today = new Date().toISOString().split('T')[0]

const validationSchema = toTypedSchema(
    z.object({
      number: z.string().min(1, 'Invoice number is required'),
      supplier_name: z.string().min(1, 'Supplier name is required'),
      supplier_tax_id: z.string().min(1, 'Tax ID is required'),
      net_amount: z.number({ invalid_type_error: 'Net amount is required' }).positive('Net amount must be > 0'),
      vat_amount: z.number({ invalid_type_error: 'VAT amount is required' }).min(0, 'VAT must be >= 0'),
      issue_date: z.string().min(1, 'Issue date is required'),
      due_date: z.string().nullable().optional(),
    }).refine((data) => {
      if (data.issue_date && data.due_date) {
        return new Date(data.due_date) >= new Date(data.issue_date)
      }
      return true
    }, {
      message: 'Due date must be greater than or equal to Issue date',
      path: ['due_date'],
    })
)

const { handleSubmit, errors, isSubmitting, defineField } = useForm({
  validationSchema,
  initialValues: {
    number: '',
    supplier_name: '',
    supplier_tax_id: '',
    net_amount: 0,
    vat_amount: 0,
    issue_date: today,
    due_date: '',
  },
})

const [number, numberProps] = defineField('number')
const [supplier_name, supplierNameProps] = defineField('supplier_name')
const [supplier_tax_id, supplierTaxIdProps] = defineField('supplier_tax_id')
const [net_amount, netAmountProps] = defineField('net_amount')
const [vat_amount, vatAmountProps] = defineField('vat_amount')
const [issue_date, issueDateProps] = defineField('issue_date')
const [due_date, dueDateProps] = defineField('due_date')

const computedGross = computed(() => {
  const net = Number(net_amount.value) || 0
  const vat = Number(vat_amount.value) || 0
  return (net + vat).toFixed(2)
})

const onSubmit = handleSubmit(async (values) => {
  try {
    const payload = {
      ...values,
      gross_amount: Number(computedGross.value),
      currency: config.public.defaultCurrency,
      status: 'pending',
    }

    await $api('/invoices', {
      method: 'POST',
      body: payload,
    })

    navigateTo('/invoices')
  } catch (err) {
    alert('Failed to create invoice: ' + (err.data?.message || err.message))
  }
})
</script>
