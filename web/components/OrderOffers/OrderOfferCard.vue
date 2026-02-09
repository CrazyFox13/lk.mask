<template>
  <div>
    <el-card class="offer" v-bind:class="{'declined':currentStatus==='declined'}">
      <div class="offer-user">
        <div class="offer-user-avatar">
          <img alt="avatar" :src="offer.company?.boss.avatar || offer.user.avatar || DefaultAvatar"/>
        </div>
        <div class="offer-user-details">
          <div class="text-gray text-small">{{ ruMoment(offer.created_at).format("DD MMM YYYY") }}</div>
          <nuxt-link :to="`/companies/${offer.company_id}`" class="text-h4 offer-user-details-company"
                     v-if="offer.company">
            {{ offer.company.title }}
          </nuxt-link>
          <div class="offer-user-details-employee">
            {{ offer.user.name }} {{ offer.user.surname }}
          </div>
        </div>
      </div>
      <div class="offer-details">
        <OrderPrices
            :amount_account="offer?.amount_account"
            :amount_account_vat="offer?.amount_account_vat"
            :amount_cash="offer?.amount_cash"
        />
        <div class="offer-details-date">
          <SvgIcon name="clock" class="mr-2"/>
          <div>Готов приступать с {{ moment(offer.date_start).format("DD.MM.YYYY") }}</div>
        </div>
      </div>
      <div v-if="offer.comment" class="offer-comment">
        {{ offer.comment }}
      </div>
      <div v-if="offer.decline_reason && currentStatus === 'declined'" class="offer-decline-reason">
        <div class="text-h5 mb-5">Причина отказа:</div>
        <p class="mb-0 text-gray">{{ declineReasonLabel }}</p>
      </div>

      <!-- Блок для заказчика: кнопка скачать заявку и форма загрузки подписанного документа -->
      <div v-if="isCustomerCompany && currentStatus === 'accepted'" class="offer-documents-section">
        <div class="offer-documents-actions">
          <el-button type="primary" @click="downloadPrintFormHandler()">
            <SvgIcon name="download" class="mr-2"/>
            Скачать заявку
          </el-button>
          <el-button type="primary" plain @click="downloadInvoiceHandler()" v-if="offer.invoice">
            <SvgIcon name="download" class="mr-2"/>
            Скачать счет
          </el-button>
          <FileUploader 
            label="Загрузить подписанный документ"
            icon="upload-input"
            @file_data="onSignedDocumentUploaded"
          />
        </div>
        <div class="offer-documents-hint text-gray mt-10">
          Вам необходимо распечатать заявку, подписать и загрузить в сервис.
        </div>
        <div v-if="offer.signed_document" class="offer-signed-document mt-10">
          <div class="text-h5 mb-5">Подписанный документ загружен</div>
          <el-button link @click="downloadSignedDocument()">
            <SvgIcon name="download" class="mr-2"/>
            Скачать документ
          </el-button>
        </div>
      </div>

      <!-- Блок для поставщика: просмотр загруженного документа и кнопка перевести в работу -->
      <div v-if="isSupplierCompany && currentStatus === 'accepted'" class="offer-documents-section">
        <div v-if="offer.signed_document" class="offer-signed-document">
          <div class="text-h5 mb-10">Подписанный документ прикреплен</div>
          <div class="offer-document-actions mb-15">
            <el-button type="primary" plain @click="previewSignedDocument()">
              <SvgIcon name="eye" class="mr-2"/>
              Просмотреть
            </el-button>
            <el-button type="primary" plain @click="downloadSignedDocument()">
              <SvgIcon name="download" class="mr-2"/>
              Скачать
            </el-button>
          </div>
          <el-button type="primary" @click="setOrderInProgressHandler()">
            Перевести заявку в работу
          </el-button>
        </div>
        <div v-else class="offer-waiting-document text-gray">
          Ожидание загрузки подписанного документа от заказчика
        </div>
      </div>

      <div class="offer-actions">
        <div class="offer-actions-buttons">

          <el-link
              v-if="offer.user"
              :href="`tel:+${offer.user.phone}`"
              target="_blank"
          >
            <SvgIcon width="24px" name="phone" class="mr-1 text-gray"/>
            Позвонить
          </el-link>

          <el-button link @click="confirmDeleteDialog=true" v-if="currentStatus!=='declined'">
            <SvgIcon
                width="24px"
                name="cancel"
                class="mr-1 text-gray"
            />
            Отклонить
          </el-button>
          <el-button link @click.prevent="confirmRevertDialog=true" v-if="currentStatus==='declined'" class="text-black">
            <SvgIcon
                width="24px"
                name="refresh"
                class="mr-1 text-gray"
            />
            <b>Вернуть</b>
          </el-button>


          <el-button class="btn-accept" v-bind:class="{'accepted':currentStatus === 'accepted'}" link
                     @click="onAccept()">
            <SvgIcon
                width="24px"
                name="check-circle"
                class="mr-1"
            />
            {{ currentStatus === 'accepted' ? 'Предложение принято' : 'Принять предложение' }}
          </el-button>
        </div>
        <div class="offer-actions-created text-gray hidden-sm-and-down">
          {{ ruMoment(offer.created_at).fromNow() }}
        </div>

        <div class="offer-badge-new" v-if="!offer.viewed_at"></div>
      </div>
    </el-card>

    <AlarmDialog v-if="confirmDeleteDialog">
      <template #header>
        <div class="confirmation-header">
          <el-button style="display: inline-flex" class="text-black" circle text @click="closeDeclineDialog()">
            <SvgIcon name="close"/>
          </el-button>
        </div>
      </template>
      <template #body>
        <div class="text-center confirmation-body">
          <div class="text-black modal-form-title m-0 text-center mb-20">Укажите причины отказа.</div>
          
          <div class="decline-reason-select mb-20">
            <el-select 
              v-model="declineReason" 
              placeholder="Выберите причину отказа"
              style="width: 100%"
              size="large">
              <el-option 
                label="Отменились работы" 
                value="works_canceled" />
              <el-option 
                label="Нашли технику" 
                value="found_equipment" />
              <el-option 
                label="Высокая стоимость" 
                value="high_price" />
              <el-option 
                label="Не устроили сроки" 
                value="bad_terms" />
              <el-option 
                label="Другое" 
                value="other" />
            </el-select>
          </div>

          <div class="confirmation-actions">
            <el-button 
              type="primary" 
              @click="onDecline()"
              :disabled="!declineReason">
              Отклонить
            </el-button>
            <el-button 
              type="primary" 
              plain 
              @click="closeDeclineDialog()">
              Отменить
            </el-button>
          </div>
        </div>
      </template>
    </AlarmDialog>


    <AlarmDialog v-if="confirmRevertDialog">
      <template #header>
        <div class="confirmation-header">
          <el-button style="display: inline-flex" class="text-black" circle text @click="confirmRevertDialog=false">
            <SvgIcon name="close"/>
          </el-button>
        </div>
      </template>
      <template #body>
        <div class="text-center confirmation-body">
          <div class="text-black modal-form-title m-0 text-center">Вы уверены, что хотите вернуть предложение на
            рассмотрение?</div>
          <div class="confirmation-actions">
            <el-button type="primary" @click.prevent="onRevert()">Вернуть</el-button>
            <el-button type="primary" plain @click.prevent="confirmRevertDialog=false">Отменить</el-button>
          </div>
        </div>
      </template>
    </AlarmDialog>

  </div>
</template>

<script setup lang="ts">
import {ruMoment, ref, computed, watch, onMounted, ElMessage} from "#imports";
import {type IOrderOffer, useOrderStore} from "~/stores/order";
import {storeToRefs} from "pinia";
import OrderPrices from "~/components/Orders/OrderPrices.vue";
import moment from "moment";
import SvgIcon from "~/components/SvgIcon.vue";
import AlarmDialog from "~/components/Common/AlarmDialog.vue";
import FileUploader from "~/components/Common/Forms/FileUploader.vue";
import DefaultAvatar from "~/assets/images/default_company_avatar.png";
import {useAuthStore} from "~/stores/user";

const props = defineProps({
  offer: {
    type: Object as () => IOrderOffer,
    required: true,
  }
});
const confirmDeleteDialog = ref(false)
const confirmRevertDialog = ref(false)
const declineReason = ref<string | null>(null)
const store = useOrderStore();
const {acceptOffer, undoAcceptOffer, declineOffer, revertOffer, downloadPrintForm, uploadSignedDocument, downloadDocument, previewDocument, setOrderInProgress, downloadInvoice} = store;
// Используем storeToRefs для сохранения реактивности
const {acceptedOffers} = storeToRefs(store);
const emit = defineEmits(['close', 'accepted', 'undo-accepted', 'declined', 'reverted', 'document-uploaded', 'order-in-progress']);

const {user} = useAuthStore();
const isCustomerCompany = computed(() => {
  return user?.company?.company_type_id === 3;
});
const isSupplierCompany = computed(() => {
  return user?.company && user.company.company_type_id !== 3;
});

const declineReasonLabels: Record<string, string> = {
  'works_canceled': 'Отменились работы',
  'found_equipment': 'Нашли технику',
  'high_price': 'Высокая стоимость',
  'bad_terms': 'Не устроили сроки',
  'other': 'Другое'
}

const declineReasonLabel = computed(() => {
  if (!props.offer?.decline_reason) return null;
  return declineReasonLabels[props.offer.decline_reason] || props.offer.decline_reason;
});

// Вычисляем текущий статус - проверяем глобальное хранилище, затем props
const currentStatus = computed(() => {
  // Если offer ID есть в глобальном хранилище принятых offers, статус 'accepted'
  if (props.offer.id && acceptedOffers.value && acceptedOffers.value.has && acceptedOffers.value.has(props.offer.id)) {
    return 'accepted';
  }
  // Иначе используем статус из props
  return props.offer.status;
});

// Инициализируем глобальное хранилище при монтировании компонента
onMounted(() => {
  // Если offer уже имеет статус 'accepted', добавляем в хранилище
  if (props.offer.status === 'accepted' && props.offer.id && acceptedOffers.value && acceptedOffers.value.add) {
    acceptedOffers.value.add(props.offer.id);
  }
});

// Отслеживаем изменения props.offer.status и синхронизируем с глобальным хранилищем
watch(() => props.offer.status, (newStatus, oldStatus) => {
  if (!acceptedOffers.value || !acceptedOffers.value.add || !acceptedOffers.value.delete) {
    return;
  }
  
  // Если реальный статус стал 'accepted', добавляем в глобальное хранилище
  if (newStatus === 'accepted' && props.offer.id) {
    acceptedOffers.value.add(props.offer.id);
  }
  // Удаляем из хранилища только если:
  // 1. Статус был 'accepted' и стал другим
  // 2. И предложение НЕ находится в acceptedOffers (т.е. не было принято локально)
  // Это предотвращает удаление при обновлении данных, когда сервер еще не успел обновить статус
  else if (oldStatus === 'accepted' && newStatus !== 'accepted' && props.offer.id) {
    // Если предложение есть в acceptedOffers, значит оно было принято локально - не удаляем
    // Удаляем только если его там нет (т.е. статус действительно изменился на сервере)
    if (!acceptedOffers.value.has(props.offer.id)) {
      acceptedOffers.value.delete(props.offer.id);
    }
  }
}, { immediate: false });

const onAccept = () => {
  if (!acceptedOffers.value || !acceptedOffers.value.add || !acceptedOffers.value.delete) {
    // Если хранилище не инициализировано, используем старую логику
    if (currentStatus.value === 'accepted') {
      undoAcceptOffer(props.offer).then(() => {
        emit("close");
        emit("undo-accepted");
      });
    } else {
      acceptOffer(props.offer).then(() => {
        console.log('Предложение принято (старая логика), эмитим событие accepted', props.offer.id);
        emit("accepted", props.offer.id);
        emit("close");
      }).catch((error) => {
        console.error('Ошибка при принятии предложения (старая логика):', error);
        ElMessage.error(error?.body?.message || error?.message || 'Ошибка при принятии предложения. Попробуйте обновить страницу.');
      });
    }
    return;
  }

  if (currentStatus.value === 'accepted') {
    // Если уже принят, отменяем выбор
    if (props.offer.id) {
      acceptedOffers.value.delete(props.offer.id);
    }
    undoAcceptOffer(props.offer).then(() => {
      emit("close");
      emit("undo-accepted");
    }).catch(() => {
      // В случае ошибки возвращаем в хранилище
      if (props.offer.id && acceptedOffers.value.add) {
        acceptedOffers.value.add(props.offer.id);
      }
    })
  } else {
    // Принимаем предложение - сразу добавляем в глобальное хранилище
    if (props.offer.id && acceptedOffers.value.add) {
      acceptedOffers.value.add(props.offer.id);
    }
    acceptOffer(props.offer).then(() => {
      console.log('Предложение принято, эмитим событие accepted', props.offer.id);
      emit("accepted", props.offer.id); // Передаем ID offer для обновления в родительском компоненте
      emit("close");
    }).catch((error) => {
      console.error('Ошибка при принятии предложения:', error);
      // В случае ошибки удаляем из хранилища
      if (props.offer.id && acceptedOffers.value.delete) {
        acceptedOffers.value.delete(props.offer.id);
      }
      // Показываем ошибку пользователю
      ElMessage.error(error?.body?.message || error?.message || 'Ошибка при принятии предложения. Попробуйте обновить страницу.');
    })
  }
}

const closeDeclineDialog = () => {
  confirmDeleteDialog.value = false;
  declineReason.value = null;
}

const onDecline = () => {
  if (currentStatus.value === 'declined') return;
  if (!declineReason.value) {
    console.error('Причина отказа не выбрана');
    return;
  }
  
  console.log('Отклонение предложения:', {
    offerId: props.offer.id,
    declineReason: declineReason.value
  });
  
  declineOffer(props.offer, declineReason.value).then(() => {
    console.log('Предложение успешно отклонено');
    emit("declined");
    closeDeclineDialog();
  }).catch((error) => {
    console.error('Ошибка при отклонении предложения:', error);
    // Показываем ошибку пользователю
    ElMessage.error(error?.body?.message || 'Ошибка при отклонении предложения');
  })
}
const onRevert = async () => {
  console.log('onRevert вызван', {
    offerId: props.offer.id,
    currentStatus: currentStatus.value,
    offerStatus: props.offer.status
  });
  
  if (currentStatus.value !== 'declined') {
    console.warn('Предложение не в статусе declined, текущий статус:', currentStatus.value);
    return;
  }
  
  try {
    await revertOffer(props.offer);
    console.log('Предложение успешно возвращено');
    ElMessage.success('Предложение возвращено на рассмотрение');
    emit("reverted");
    confirmRevertDialog.value = false;
  } catch (error: any) {
    console.error('Ошибка при возврате предложения:', error);
    ElMessage.error(error?.body?.message || error?.message || 'Ошибка при возврате предложения');
  }
}

const onSignedDocumentUploaded = async (fileData: any) => {
  try {
    await uploadSignedDocument(props.offer, fileData);
    ElMessage.success('Документ успешно загружен');
    emit("document-uploaded");
    // Обновляем данные предложения
    if (props.offer.id) {
      // Можно обновить локально или перезагрузить данные
      props.offer.signed_document = fileData;
    }
  } catch (error: any) {
    ElMessage.error(error?.body?.message || 'Ошибка при загрузке документа');
  }
}

const downloadPrintFormHandler = () => {
  downloadPrintForm(props.offer);
}

const downloadSignedDocument = () => {
  if (!props.offer.signed_document?.id) return;
  downloadDocument(props.offer, props.offer.signed_document.id);
}

const previewSignedDocument = () => {
  if (!props.offer.signed_document?.id) return;
  previewDocument(props.offer, props.offer.signed_document.id);
}


const setOrderInProgressHandler = async () => {
  try {
    await setOrderInProgress(props.offer);
    ElMessage.success('Заявка переведена в работу');
    emit("order-in-progress");
  } catch (error: any) {
    ElMessage.error(error?.body?.message || 'Ошибка при переводе заявки в работу');
  }
}

</script>


<style scoped lang="scss">
.offer {
  position: relative;

  &.declined {
    filter: grayscale(.8);
    color: grey;
  }

  &-user {
    display: flex;
    column-gap: 16px;
    align-items: center;
    margin-bottom: 16px;

    &-avatar {
      img {
        width: 64px;
      }
    }

    &-details {
      display: flex;
      flex-direction: column;
    }
  }

  &-details {
    display: flex;
    column-gap: 16px;
    margin-bottom: 16px;
    flex-direction: column;
    row-gap: 8px;

    @media (min-width: 992px) {
      flex-direction: row;
      align-items: center;
    }

    &-date {
      display: flex;
      align-items: center;
    }
  }

  &-comment {
    margin-bottom: 16px;
  }

  &-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;

    &-buttons {
      //display: flex;
      align-items: center;
      column-gap: 8px;
      row-gap: 12px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      width: 100%;
      flex: 1;

      .el-button, .el-link {
        margin: 0;
        order: 2;

        &:last-child {
          grid-column-start: 1;
          grid-column-end: 3;
        }
      }

      @media (min-width: 992px) {
        display: flex;
        .el-button {

          &:last-child {
            order: 1;
          }
        }
        .el-link {
          order: 3;
        }

        .btn-accept {
          &.accepted {
            span {
              color: #EB8A00;
            }
          }
        }
      }

      @media (max-width: 991px) {
        .btn-accept {
          border: 2px solid;
          border-color: #EB8A00;
          padding: 9px 12px;

          &.accepted {
            background-color: #FAB80F;
            border-color: #FAB80F;
            color: black;
          }
        }
      }
    }
  }

  &-badge-new {
    display: block;
    width: 20px;
    height: 20px;
    background: #F92609;
    border-radius: 20px;
    position: absolute;
    right: 20px;
    top: 20px;
  }

  &-decline-reason {
    margin-bottom: 16px;
    padding: 12px;
    background-color: #f5f5f5;
    border-radius: 6px;
  }
}

.decline-reason-select {
  max-width: 400px;
  margin: 0 auto;
}

.confirmation-actions {
  display: flex;
  gap: 10px;
  justify-content: center;
  flex-wrap: wrap;
}

.offer-documents-section {
  margin-top: 20px;
  padding: 16px;
  background-color: #f9f9f9;
  border-radius: 8px;
  margin-bottom: 16px;
}

.offer-documents-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;

  @media (min-width: 768px) {
    flex-direction: row;
    align-items: center;
  }
}

.offer-documents-hint {
  font-size: 14px;
  line-height: 1.5;
}

.offer-signed-document {
  padding-top: 16px;
  border-top: 1px solid #e0e0e0;
}

.offer-document-actions {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.offer-waiting-document {
  font-size: 14px;
  font-style: italic;
}
</style>