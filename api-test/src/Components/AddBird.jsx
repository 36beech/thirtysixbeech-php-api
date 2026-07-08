import { useEffect, useMemo, useState } from "react";
import { useApi } from "./useApi";
import { Select } from "./Input";

export const AddBird = ({ onAddBird, token }) => {
  const { get, post, loading, error } = useApi(token);
  const [families, setFamilies] = useState([]);

  let familyOptions = [];
  useMemo(() => {
    familyOptions = families.map((family) => {
      return { value: family.id, label: family.common_name };
    });
  }, [families]);

  useEffect(() => {
    post("/birds", { bird: "Test" })
      .then((result) => {
        console.log("AddBird", result);
      })
      .catch((error) => console.log(error));
  }, [post]);

  useEffect(() => {
    get("/families").then((newFamilies) => setFamilies(newFamilies?.data));
  }, [get]);

  return (
    <>
      <h2>Add bird</h2>
      <Select options={familyOptions} />
    </>
  );
};

/*
    `species`.`family_id`,
    `species`.`common_name`,
    `species`.`scientific_name`,
    `species`.`conservation_status`,
    `species`.`avg_wingspan_cm`,
    `species`.`avg_weight_g`,
    `species`.`migratory`,
    `species`.`habitat`,
    */
