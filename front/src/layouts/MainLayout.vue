<template>
  <q-layout view="lHh Lpr lFf">
    <q-header>
      <q-toolbar class="bg-white text-black">
        <div class="q-ma-md">
          <q-img src="sofia-logo.svg" width="100px" />
        </div>
        <q-space />
        <q-tabs shrink>
          <q-tab label="Nuestro Equipo" no-caps class="text-bold" active />
          <q-tab label="Nuestros Servicios" no-caps class="text-bold" active />
          <q-tab label="Contacto" no-caps class="text-bold" active />
        </q-tabs>
      </q-toolbar>
    </q-header>
    <q-page-container>
      <q-page>
        <div class="row">
          <div class="col-12">
            <div class="text-h6 text-bold text-primary text-center">Puedes descargar tus facturas en formato PDF</div>
          </div>
          <div class="col-12 col-md-4"></div>
          <div class="col-12 col-md-4 q-pa-xs">
            <q-form @submit="consulta">
              <q-input outlined :loading="loading" clearable counter rounded v-model="search" placeholder="Buscar CI NIT" :rules="[ val => val.length > 0 || 'Ingrese un CI NIT']" />
            </q-form>
          </div>
          <div class="col-12 col-md-4"></div>
          <div class="col-12" v-if="facturas.length > 0">
            <q-table :rows="facturas" :rows-per-page-options="[0]" :columns="column" :loading="loading">
              <template v-slot:body-cell-pdf="props">
                <q-td :props="props" >
<!--                  {{props.row}}-->
                  <q-btn dense color="red" label="PDF" icon="download" type="a" target="_blank" :href="url+'facturaPdf/'+props.row.CodAut" />
                  <br>
                  <q-btn dense color="blue" label="SIAT" no-caps icon="fa-regular fa-file-pdf"
                         type="a" target="_blank"
                          :href="`https://siat.impuestos.gob.bo/consulta/QR?nit=3779602010&cuf=${props.row.cuffac}&numero=${props.row.nrofac}&t=2`" />
                </q-td>
              </template>
            </q-table>
<!--            <pre>{{facturas}}</pre>-->
          </div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script lang="ts">
import { defineComponent } from 'vue'
import moment from 'moment'

export default defineComponent({
  name: 'MainLayout',
  data () {
    return {
      search: '',
      url: 'https://bsofiafactura.tuprogam.com/api/',
      facturas: [],
      loading: false,
      column: [
        {
          name: 'pdf',
          label: 'PDF',
          field: 'pdf',
          align: 'left'
        },
        {
          name: 'FechaCan',
          label: 'Fecha',
          field: (row: { FechaFac: moment.MomentInput }) => moment(row.FechaFac).format('DD/MM/YYYY'),
          align: 'left',
          sortable: true
        },
        {
          name: 'nrofac',
          label: 'Numero',
          field: 'nrofac',
          align: 'left',
          sortable: true
        },
        {
          name: 'CINIT',
          label: 'NIT',
          field: 'IdCli',
          align: 'left',
          sortable: true
        }
        // {
        //   name: 'nombre',
        //   label: 'Nombre',
        //   field: 'nombre',
        //   align: 'left',
        //   sortable: true
        // },
      ]
    }
  },
  methods: {
    consulta () {
      this.loading = true
      this.$axios.post('consulta', {
        ci: this.search
      }).then((response) => {
        console.log(response.data)
        this.facturas = response.data
      }).catch((error) => {
        console.log(error)
      }).finally(
        () => {
          this.loading = false
        }
      )
    },
    generarPdf (factura: { CodAut: string }) {
      console.log(factura)
      // eslint-disable-next-line @typescript-eslint/no-empty-function
      this.$axios.post('facturaPdf/' + factura.CodAut).then(() => {
      })
    }
  }
})
</script>
