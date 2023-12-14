import torch
from transformers import GPT2Tokenizer, GPT2LMHeadModel, TextDataset, DataCollatorForLanguageModeling, TrainingArguments, Trainer

tokenizer = GPT2Tokenizer.from_pretrained("ClassCat/gpt2-base-french")
model = GPT2LMHeadModel.from_pretrained("ClassCat/gpt2-base-french")

flag = "IUT{D3F1N1T3LY_N0T_TR41N_0N_K4RL_M4RX}"
new_tokens = [char for char in flag]
tokenizer.add_tokens(new_tokens)
model.resize_token_embeddings(len(tokenizer))

train_dataset = TextDataset(tokenizer=tokenizer, file_path="dataset.txt", block_size=256)
data_collator = DataCollatorForLanguageModeling(tokenizer=tokenizer, mlm=False)

training_args = TrainingArguments(
    per_device_train_batch_size=8,
    num_train_epochs=2,
    logging_dir='./logs',
    logging_steps=50,
    save_steps=50,
    output_dir='/tmp/ai_output',
    overwrite_output_dir=True,
    save_total_limit=1,
    fp16=True,
    learning_rate=40e-5
)

trainer = Trainer(
    model=model,
    args=training_args,
    data_collator=data_collator,
    train_dataset=train_dataset,
)

trainer.train()

trainer.save_model('./final_model')
tokenizer.save_pretrained('./final_model')
