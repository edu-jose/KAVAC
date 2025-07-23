<template>
    <div class="row">
        <div class="col-12 my-2">
            <div class="form-group">
                <label
                    class="control-label"
                    :for="id"
                >
                    {{ label }}
                </label>
                <div class="custom-file">
                    <input
                        type="file"
                        :name="name"
                        :id="id"
                        class="custom-file-input form-control-sm"
                        data-toggle="tooltip"
                        title="Seleccione las imágenes"
                        @change="onFileChange"
                        multiple
                        accept="image/*"
                    />
                    <label class="custom-file-label" for="customFile">Cargar</label>
                </div>
                <small id="customFileHelp" class="form-text text-muted">
                    Archivos permitidos: jpg, jpeg, png
                </small>
            </div>
        </div>
        <div class="col-12" v-if="images.length > 0">
            <div class="accordion accordion-gallery" :id="'preview'+id">
                <div class="card shadow-none">
                    <div
                        class="card-header" :id="'headingPreview'+id"
                        data-toggle="tooltip"
                        :title="'Presione para mostrar/ocultar la vista previa de las ' + label"
                    >
                        <h6 class="mb-0 text-center">
                            <button
                                class="btn btn-link btn-block text-center accordion-button"
                                type="button"
                                data-toggle="collapse"
                                :data-target="'#collapsePreview'+id" aria-expanded="true"
                                :aria-controls="'collapsePreview'+id"
                            >
                                <span class="card-title">
                                    Vista previa de las {{ label }}
                                </span>
                                <i class="fa fa-angle-down text-info" aria-hidden="true"></i>
                            </button>
                        </h6>
                    </div>
                    <div
                        :id="'collapsePreview'+id"
                        class="collapse show"
                        :aria-labelledby="'headingPreview'+id"
                        :data-parent="'#preview'+id"
                    >
                        <div class="card-body p-0">
                            <div class="row">
                                <div
                                    class="text-center my-2"
                                    :class="cols"
                                    v-for="(image, index) in images" :key="index"
                                >
                                    <img :src="image" class="gallery-thumbnail" alt="" />
                                    <div>
                                        <a
                                            href="javascript:void(0)"
                                            class="btn btn-neutral btn-sm"
                                            @click="removeImage(index)"
                                            title="Eliminar imagen"
                                            data-toggle="tooltip"
                                        >
                                            <i class="fa fa-times text-danger"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                images: [],
                fileList: []
            };
        },
        props: {
            name: {
                type: String,
                required:false,
                default: 'images'
            },
            id: {
                type: String,
                required: false,
                default: 'images'
            },
            label: {
                type: String,
                required: false,
                default: 'Imágenes'
            },
            cols: {
                type: String,
                required: false,
                default: 'col-6 col-md-4 col-lg-3'
            },
        },
        methods: {
            onFileChange(event) {
                const _self = this;
                const files = event.target.files;

                for (const file of files) {
                    const reader = new FileReader();

                    reader.onload = (e) => {
                        _self.images.push(e.target.result); // Agregar la imagen a la lista
                        _self.fileList.push(file); // Agregar los archivos seleccionados a la lista
                        _self.syncParentImages(); // Sincronizar con el padre
                    };

                    reader.readAsDataURL(file); // Leer el archivo como URL de datos
                }
            },
            removeImage(index) {
                const _self = this;
                bootbox.confirm({
                    message: "¿Está seguro de eliminar esta imagen?",
                    buttons: {
                        confirm: {
                            label: 'Eliminar',
                            className: 'btn-danger'
                        },
                        cancel: {
                            label: 'Cancelar',
                            className: 'btn-secondary'
                        }
                    },
                    callback: (result) => {
                        if (result) {
                            _self.images.splice(index, 1);
                            _self.fileList.splice(index, 1);
                            _self.syncParentImages();
                        }
                    }
                });
            },
            syncParentImages: function() {
                let images = this.fileList;
                this.$emit('syncImages', images);
            },
        },
    }
</script>
