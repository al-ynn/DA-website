<script setup lang="ts">
import { ref } from 'vue'

const form = ref({
  customerName: '',
  address: '',
  contact: '',
  email: '',
  samplingDate: '',
  samples: [
    {
      sampleId: '',
      description: '',
      topography: '',
      longitude: '',
      latitude: '',
      barangay: '',
      municipality: '',
      province: '',
      farmArea: '',
      crops: '',
      remarks: '',
      analysis: '',
    },
  ],
})

function addRow() {
  form.value.samples.push({
    sampleId: '',
    description: '',
    topography: '',
    longitude: '',
    latitude: '',
    barangay: '',
    municipality: '',
    province: '',
    farmArea: '',
    crops: '',
    remarks: '',
    analysis: '',
  })
}
</script>

<template>
  <div class="bg-white text-black p-6 text-xs">

    <!-- HEADER -->
    <div class="text-center mb-4">
      <h2 class="font-bold text-sm">TEST REQUEST FORM</h2>
      <p>Regional Soils Laboratory</p>
    </div>

    <!-- CUSTOMER INFO -->
    <div class="border p-3 mb-4">
      <h3 class="font-semibold mb-2">Customer Information</h3>

      <div class="grid grid-cols-2 gap-2">
        <input v-model="form.customerName" placeholder="Customer Name" class="border p-1" />
        <input v-model="form.address" placeholder="Address" class="border p-1" />
        <input v-model="form.contact" placeholder="Contact Number" class="border p-1" />
        <input v-model="form.email" placeholder="Email" class="border p-1" />
        <input v-model="form.samplingDate" type="date" class="border p-1 col-span-2" />
      </div>
    </div>

    <!-- TABLE -->
    <div class="overflow-x-auto border">
      <table class="w-full border-collapse">
        <thead>
          <tr class="bg-gray-200 text-[10px]">
            <th class="border p-1">#</th>
            <th class="border p-1">Sample ID</th>
            <th class="border p-1">Description</th>
            <th class="border p-1">Topography</th>
            <th class="border p-1">Longitude</th>
            <th class="border p-1">Latitude</th>
            <th class="border p-1">Barangay</th>
            <th class="border p-1">Municipality</th>
            <th class="border p-1">Province</th>
            <th class="border p-1">Farm Area</th>
            <th class="border p-1">Crops</th>
            <th class="border p-1">Remarks</th>
            <th class="border p-1">Analysis</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="(row, index) in form.samples"
            :key="index"
          >
            <td class="border p-1 text-center">{{ index + 1 }}</td>

            <td class="border p-1">
              <input v-model="row.sampleId" class="w-full" />
            </td>

            <td class="border p-1">
              <input v-model="row.description" class="w-full" />
            </td>

            <td class="border p-1">
              <input v-model="row.topography" class="w-full" />
            </td>

            <td class="border p-1">
              <input v-model="row.longitude" class="w-full" />
            </td>

            <td class="border p-1">
              <input v-model="row.latitude" class="w-full" />
            </td>

            <td class="border p-1">
              <input v-model="row.barangay" class="w-full" />
            </td>

            <td class="border p-1">
              <input v-model="row.municipality" class="w-full" />
            </td>

            <td class="border p-1">
              <input v-model="row.province" class="w-full" />
            </td>

            <td class="border p-1">
              <input v-model="row.farmArea" class="w-full" />
            </td>

            <td class="border p-1">
              <input v-model="row.crops" class="w-full" />
            </td>

            <td class="border p-1">
              <input v-model="row.remarks" class="w-full" />
            </td>

            <td class="border p-1">
              <input v-model="row.analysis" class="w-full" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- ADD ROW -->
    <div class="mt-3">
      <button
        @click="addRow"
        class="bg-[#0E3D1A] text-white px-4 py-1 text-xs"
      >
        + Add Row
      </button>
    </div>

    <!-- SIGNATURE -->
    <div class="grid grid-cols-2 gap-6 mt-8">
      <div>
        <p>________________________</p>
        <p>Name and Signature of Customer</p>
      </div>

      <div>
        <p>________________________</p>
        <p>Received by</p>
      </div>
    </div>

  </div>
</template>
