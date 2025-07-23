<template>
	<div>
		<h6 class="card-title">
			Números Telefónicos&#160;
			<i
                class="fa fa-plus-circle cursor-pointer" @click="addPhone"
                title="Agregar número telefónico" data-toggle="tooltip"
            ></i>
		</h6>
		<div class="row phone-row" v-for="(phone, index) in phones" :key="index">
			<div class="col-3">
				<div class="form-group is-required">
                    <select2
                        data-toggle="tooltip"
                        v-model="phone.type"
                        name="phone_type[]"
                        class="select2"
                        title="Seleccione el tipo de número telefónico"
                        :options="phoneTypes"
                    ></select2>
				</div>
			</div>
			<div class="col-2">
				<div class="form-group is-required">
					<input
                        type="text" placeholder="Cod. Area" data-toggle="tooltip" name="phone_area_code[]"
                        title="Indique el código de área" v-model="phone.area_code" class="form-control input-sm"
                        v-is-digits
                    >
				</div>
			</div>
			<div class="col-4">
				<div class="form-group is-required">
					<input
                        type="text" placeholder="Número" data-toggle="tooltip" name="phone_number[]"
                        title="Indique el número telefónico" v-model="phone.number" class="form-control input-sm"
                        v-is-digits
                    >
				</div>
			</div>
			<div class="col-2">
				<div class="form-group">
					<input
                        type="text" placeholder="Extensión" data-toggle="tooltip" name="phone_extension[]"
						title="Indique la extención telefónica (opcional)" v-model="phone.extension"
						class="form-control input-sm" v-is-digits
                    >
				</div>
			</div>
			<div class="col-1">
				<div class="form-group">
					<button
                        class="btn btn-sm btn-danger btn-action" type="button"
                        @click="removeRow(index, phones)"
						title="Eliminar este dato" data-toggle="tooltip"
                    >
						<i class="fa fa-minus-circle"></i>
					</button>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
	export default {
		data() {
			return {
				phones: [],
                phoneTypes: [
                    { id: '', text: 'Seleccione...' },
                    { id: 'M', text: 'Móvil' },
                    { id: 'T', text: 'Teléfono' },
                    { id: 'F', text: 'Fax' }
                ]
			}
		},
		watch: {
			phones: function() {
				localStorage.removeItem('phones');
				if (this.phones) {
					localStorage.phones = JSON.stringify(this.phones);
				}
                this.syncParentPhones();
			}
		},
		props: ['initial_data'],
		methods: {
            /**
             * Agrega un campo para introducir un número telefónico
             *
             * @method    addPhone
             *
             * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
             */
			addPhone: function() {
				this.phones.push({
					type: '',
					area_code: '',
					number: '',
					extension: ''
				});
                this.syncParentPhones();
			},
            syncParentPhones: function() {
                let phones = this.phones;
                this.$emit('syncPhones', phones);
            },
		},
		mounted() {
            const _self = this;
			if (_self.initial_data) {
				_self.phones = JSON.parse(_self.initial_data);
			}
            _self.syncParentPhones();
		}
	};
</script>
