<template>
    <div class="card">
        <div class="card-header">Product List <span v-show="status">{{status}}</span></div>

        <div v-if="products.length" class="card-body">
            <div v-for="(product, index) in products" @click="showDetails(index)" class="product">
                <div class="row">
                    <div class="col-md-3">
                        <strong>{{product.name}}</strong>                    
                    </div>
                    <div class="col-md-9 text-right">
                        <button
                            v-if="productsAssigned.indexOf(product.id) > -1"
                            @click.stop="removeProduct(product.id)"
                            class="btn btn-danger">Remove</button>

                        <button
                            v-else
                            @click.stop="addProduct(product.id)"
                            class="btn btn-primary">Add</button>
                    </div>
                </div>
                <div v-show="expanded.indexOf(product.id) > -1" class="row details">                        
                    <div class="col-md-2">
                        <img v-if="product.image" :src="`/storage/product_images/${product.image}`">
                        <img v-else src="/images/default_product.png">
                    </div>
                    <div class="col-md-10">
                        <p>{{product.description}}</p>
                        <p>Basically what I'm saying is that if I were you I would definitely add this product.</p>
                    </div>
                </div>  
            </div>
        </div>
    </div>            
</template>

<script>
    import axios from 'axios';

    export default {        
        props: ['userId'],
        data() {
            return {
                products: [],
                productsAssigned: [],
                expanded: [],
                status: ''
            };
        },
        created() {
            axios.get('/api/products')
                .then(response => this.products = response.data)
                .catch(error => {
                    console.log(error);
                    this.status = 'Unable to retrieve product list.';
                });

            axios.get(`/api/users/${this.userId}/products`)
                .then(response => this.productsAssigned = response.data.map(product => product.id))
                .catch(error => {
                    console.log(error);
                    this.status = 'Unable to retrieve the products assigned to your account.';
                });
            
        },
        methods: {
            showDetails(productIndex) {
                const expandedIndex = this.expanded.indexOf(this.products[productIndex].id);
                if (expandedIndex > -1) {                    
                    return this.expanded.splice(expandedIndex, 1);
                }                      
                this.expanded.push(this.products[productIndex].id);
            },
            addProduct(productId) {
                axios.post(`/api/users/${this.userId}/products`, {product_id: productId})
                    .then(response => {
                        if (response.data.success) {
                            this.productsAssigned.push(productId);
                            this.status = 'A product was added to your account.';
                        }
                    })
                    .catch(error => {
                        console.log(error);
                        this.status = 'Unable to add the product to your account.';
                    });
            },
            removeProduct(productId) {
                axios.delete(`/api/users/${this.userId}/products/${productId}`)
                    .then(response => {                             
                        if (response.data.success) {           
                            const index = this.productsAssigned.indexOf(productId);
                            this.productsAssigned.splice(index, 1);
                            this.status = 'A product was removed from your account.';                        
                        }
                    }).catch(error => {
                        console.log(error);
                        this.status = 'Unable to remove the product to your account.';
                    });
            },
        }
    }
</script>

<style scoped lang="scss">    
    .product {
        padding: 10px;
        &:not(:last-child) {        
            border-bottom: solid 1px #000000;
        }
        cursor: pointer;
    }      
    img {
        width: 180px;
        height: 180px;
    }
    p {
        margin-left: 10px;
        font-style: italic;
        font-size: 18px;
    }
    .details {
        padding-top: 10px;
        border-top: dotted 1px #000000;
        margin-top: 10px;
    }
    .card-header span {
        float: right;
        font-style: italic;
        font-weight: bold;
    }
    button {
        width: 100px;
    }
</style>
