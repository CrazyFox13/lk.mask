<template>
  <el-card>
    <div class="list">
      <div class="list-item">
        <div class="list-item-number">1</div>
        <div class="list-item-text">Подробно опишите задачу, которую нужно выполнить</div>
      </div>
      <div class="list-item">
        <div class="list-item-number">2</div>
        <div class="list-item-text">Отправьте заявку</div>
      </div>
      <div class="list-item">
        <div class="list-item-number">3</div>
        <div class="list-item-text">Согласуйте нашу форму заявки.</div>
      </div>
      <div class="list-item">
        <div class="list-item-number">4</div>
        <div class="list-item-text">Прикрепите Подписанную форму заявки и оплатите счет.</div>
      </div>
      <div class="list-item">
        <div class="list-item-number">5</div>
        <div class="list-item-text">Техника будет у вас в оговоренные сроки.</div>
      </div>
    </div>
  </el-card>
</template>

<script setup lang="ts">
</script>

<style scoped lang="scss">
.list {
  .list-item {
    display: flex;
    column-gap: 10px;
    align-items: center;
    height: 40px;
    margin-bottom: 25px;
    position: relative;

    .list-item-number {
      background: #FAB80F;
      color: white;
      font-weight: 800;
      font-size: 18px;
      line-height: 20px;
      font-family: 'Roboto Bold', sans-serif;
      padding: 10px 12px;
      border-radius: 6px;

      &::after {
        content: " ";
        display: block;
        position: absolute;
        height: 18px;
        width: 1px;
        background: rgba(154, 161, 169, 0.4);
        bottom: -21px;
        left: 16.5px;
      }


    }

    &:last-of-type {
      .list-item-number {
        &::after {
          display: none;
        }
      }
    }
  }
}
</style>