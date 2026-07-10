const inputClasses = "w-full border border-slate-800 p-4 my-4 text-lg";

export const Input = (props) => {
  return <input {...props} className={inputClasses} />;
};

export const Select = (props) => {
  const {options, onChange} = props;

  const handleSelectChange = (e) => {
    onChange(e.target.value);
  }

  return (
    <select {...props} onChange={handleSelectChange} className={inputClasses}>
      <option value={0}>Select a family</option>
      {options.map((option) => <option value={option.value}>{option.label}</option>)}
    </select>
  );
};
