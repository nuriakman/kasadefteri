<template>
  <q-page padding>
    <div class="row q-col-gutter-md">
      <!-- Kategoriler -->
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6">Kategoriler</div>
          </q-card-section>

          <q-card-section>
            <!-- Kategori Ekleme Formu -->
            <q-form @submit="handleAddCategory" class="q-gutter-sm">
              <div class="row q-col-gutter-sm">
                <div class="col">
                  <q-input
                    v-model="newCategory.name"
                    label="Kategori Adı"
                    dense
                    :rules="[val => !!val || 'Kategori adı gerekli']"
                  />
                </div>
                <div class="col-auto">
                  <q-select
                    v-model="newCategory.type"
                    :options="[
                      { label: 'Gelir', value: 'income' },
                      { label: 'Gider', value: 'expense' }
                    ]"
                    label="Tür"
                    dense
                    options-dense
                    :rules="[val => !!val || 'Tür seçiniz']"
                  />
                </div>
                <div class="col-auto">
                  <q-btn
                    type="submit"
                    color="primary"
                    icon="add"
                    :loading="loading.category"
                  />
                </div>
              </div>
            </q-form>

            <!-- Kategori Listesi -->
            <q-list separator class="q-mt-md">
              <q-item v-for="category in categories" :key="category.id">
                <q-item-section>
                  <q-item-label>{{ category.name }}</q-item-label>
                  <q-item-label caption>
                    {{ category.type === 'income' ? 'Gelir' : 'Gider' }}
                  </q-item-label>
                </q-item-section>

                <q-item-section side>
                  <q-btn
                    flat
                    round
                    color="negative"
                    icon="delete"
                    @click="deleteCategory(category.id)"
                  />
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>

      <!-- Kasa Türleri -->
      <div class="col-12 col-md-6">
        <q-card>
          <q-card-section>
            <div class="text-h6">Kasa Türleri</div>
          </q-card-section>

          <q-card-section>
            <!-- Kasa Türü Ekleme Formu -->
            <q-form @submit="handleAddRegisterType" class="q-gutter-sm">
              <div class="row q-col-gutter-sm">
                <div class="col">
                  <q-input
                    v-model="newRegisterType.name"
                    label="Kasa Türü"
                    dense
                    :rules="[val => !!val || 'Kasa türü adı gerekli']"
                  />
                </div>
                <div class="col-auto">
                  <q-btn
                    type="submit"
                    color="primary"
                    icon="add"
                    :loading="loading.registerType"
                  />
                </div>
              </div>
            </q-form>

            <!-- Kasa Türleri Listesi -->
            <q-list separator class="q-mt-md">
              <q-item v-for="type in registerTypes" :key="type.id">
                <q-item-section>
                  <q-item-label>{{ type.name }}</q-item-label>
                </q-item-section>

                <q-item-section side>
                  <q-btn
                    flat
                    round
                    color="negative"
                    icon="delete"
                    @click="deleteRegisterType(type.id)"
                  />
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </div>

      <!-- Kullanıcı Yönetimi -->
      <div class="col-12" v-if="isSuperAdmin">
        <q-card>
          <q-card-section>
            <div class="text-h6">Kullanıcı Yönetimi</div>
          </q-card-section>

          <q-card-section>
            <!-- Kullanıcı Ekleme Formu -->
            <q-form @submit="handleAddUser" class="q-gutter-md">
              <div class="row q-col-gutter-md">
                <div class="col-12 col-md-3">
                  <q-input
                    outlined
                    v-model="newUser.userName"
                    :rules="[val => !!val || 'Kullanıcı adı zorunludur']"
                    label="Kullanıcı Adı"
                  />
                </div>
                <div class="col-12 col-md-3">
                  <q-input
                    v-model="newUser.email"
                    type="email"
                    label="E-posta"
                    :rules="[
                      val => !!val || 'E-posta gerekli',
                      val => /^[^@]+@[^@]+\.[^@]+$/.test(val) || 'Geçerli bir e-posta girin'
                    ]"
                  />
                </div>
                <div class="col-12 col-md-3">
                  <q-select
                    v-model="newUser.role"
                    :options="[
                      { label: 'Kullanıcı', value: 'user' },
                      { label: 'Admin', value: 'admin' }
                    ]"
                    label="Rol"
                    :rules="[val => !!val || 'Rol seçiniz']"
                  />
                </div>
                <div class="col-12">
                  <q-btn
                    label="Kullanıcı Ekle"
                    type="submit"
                    color="primary"
                    :loading="loading.user"
                  />
                </div>
              </div>
            </q-form>

            <!-- Kullanıcı Listesi -->
            <q-table
              :rows="users"
              :columns="userColumns"
              row-key="id"
              class="q-mt-md"
            >
              <template v-slot:body-cell-actions="props">
                <q-td :props="props">
                  <q-btn
                    flat
                    round
                    color="negative"
                    icon="delete"
                    @click="deleteUser(props.row.id)"
                  />
                </q-td>
              </template>
            </q-table>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useQuasar } from 'quasar';
import { useAuthStore } from 'src/stores/auth';
import { api } from 'boot/axios';

const $q = useQuasar();
const authStore = useAuthStore();

// Yükleme durumları
const loading = ref({
  category: false,
  registerType: false,
  user: false
});

// Yeni kayıt formları
const newCategory = ref({ name: '', type: null });
const newRegisterType = ref({ name: '' });
const newUser = ref({
  userName: '',
  email: '',
  role: 'user',
  avatar: ''
});

// Veriler
const categories = ref([]);
const registerTypes = ref([]);
const users = ref([]);

// Kullanıcı tablosu sütunları
const userColumns = [
  {
    name: 'userName',
    required: true,
    label: 'Kullanıcı Adı',
    align: 'left',
    field: 'userName',
    sortable: true
  },
  {
    name: 'email',
    required: true,
    label: 'E-posta',
    align: 'left',
    field: 'email',
    sortable: true
  },
  {
    name: 'role',
    required: true,
    label: 'Rol',
    align: 'left',
    field: row => row.role === 'admin' ? 'Admin' : 'Kullanıcı',
    sortable: true
  },
  {
    name: 'actions',
    required: true,
    label: 'İşlemler',
    align: 'right'
  }
];

// SuperAdmin kontrolü
const isSuperAdmin = computed(() => authStore.user?.role === 'superadmin');

// Verileri yükle
const loadData = async () => {
  try {
    const [categoriesRes, registerTypesRes] = await Promise.all([
      api.get('/categories'),
      api.get('/register-types')
    ]);
    
    categories.value = categoriesRes.data;
    registerTypes.value = registerTypesRes.data;

    if (isSuperAdmin.value) {
      const usersRes = await api.get('/users');
      users.value = usersRes.data;
    }
  } catch (error) {
    console.error('Veriler yüklenirken hata:', error);
    $q.notify({
      type: 'negative',
      message: 'Veriler yüklenirken hata oluştu'
    });
  }
};

// Kategori işlemleri
const handleAddCategory = async () => {
  loading.value.category = true;
  try {
    await api.post('/categories', newCategory.value);
    await loadData();
    newCategory.value = { name: '', type: null };
    $q.notify({
      type: 'positive',
      message: 'Kategori eklendi'
    });
  } catch (error) {
    console.error('Kategori eklenirken hata:', error);
    $q.notify({
      type: 'negative',
      message: 'Kategori eklenirken hata oluştu'
    });
  } finally {
    loading.value.category = false;
  }
};

const deleteCategory = async (id: number) => {
  try {
    await api.delete(`/categories/${id}`);
    await loadData();
    $q.notify({
      type: 'positive',
      message: 'Kategori silindi'
    });
  } catch (error) {
    console.error('Kategori silinirken hata:', error);
    $q.notify({
      type: 'negative',
      message: 'Kategori silinirken hata oluştu'
    });
  }
};

// Kasa türü işlemleri
const handleAddRegisterType = async () => {
  loading.value.registerType = true;
  try {
    await api.post('/register-types', newRegisterType.value);
    await loadData();
    newRegisterType.value = { name: '' };
    $q.notify({
      type: 'positive',
      message: 'Kasa türü eklendi'
    });
  } catch (error) {
    console.error('Kasa türü eklenirken hata:', error);
    $q.notify({
      type: 'negative',
      message: 'Kasa türü eklenirken hata oluştu'
    });
  } finally {
    loading.value.registerType = false;
  }
};

const deleteRegisterType = async (id: number) => {
  try {
    await api.delete(`/register-types/${id}`);
    await loadData();
    $q.notify({
      type: 'positive',
      message: 'Kasa türü silindi'
    });
  } catch (error) {
    console.error('Kasa türü silinirken hata:', error);
    $q.notify({
      type: 'negative',
      message: 'Kasa türü silinirken hata oluştu'
    });
  }
};

// Kullanıcı işlemleri
const handleAddUser = async () => {
  loading.value.user = true;
  try {
    await api.post('/users', newUser.value);
    await loadData();
    newUser.value = {
      userName: '',
      email: '',
      role: 'user',
      avatar: ''
    };
    $q.notify({
      type: 'positive',
      message: 'Kullanıcı eklendi'
    });
  } catch (error) {
    console.error('Kullanıcı eklenirken hata:', error);
    $q.notify({
      type: 'negative',
      message: 'Kullanıcı eklenirken hata oluştu'
    });
  } finally {
    loading.value.user = false;
  }
};

const deleteUser = async (id: number) => {
  try {
    await api.delete(`/users/${id}`);
    await loadData();
    $q.notify({
      type: 'positive',
      message: 'Kullanıcı silindi'
    });
  } catch (error) {
    console.error('Kullanıcı silinirken hata:', error);
    $q.notify({
      type: 'negative',
      message: 'Kullanıcı silinirken hata oluştu'
    });
  }
};

// Sayfa yüklendiğinde verileri getir
loadData();
</script>
