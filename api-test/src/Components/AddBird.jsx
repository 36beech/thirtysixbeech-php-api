import { useEffect, useMemo, useState } from "react";
import { useApi } from "./useApi";
import { Input, Select } from "./Input";
import { Button } from "./Button";

export const AddBird = ({ onAddBird, token }) => {
  const { get, post, loading, error } = useApi(token);
  const [families, setFamilies] = useState([]);

  const [familyId, setFamilyId] = useState();
  const [commonName, setCommonName] = useState("");
  const [scientificName, setScientificName] = useState("");
  const [conservationStatus, setConservationStatus] = useState("");
  const [avgWingspanCm, setAvgWingspanCm] = useState("");
  const [avgWeightG, setAvgWeightG] = useState("");
  const [habitat, setHabitat] = useState("");

  let familyOptions = useMemo(() => {
    console.log("???");
    return families.map((family) => {
      return { value: family.id, label: family.common_name };
    });
  }, [families]);

  const handleSubmit = () => {
    const newBird = {
      family_id: familyId,
      common_name: commonName,
      scientific_name: scientificName,
      conservation_status: conservationStatus,
      avg_wingspan_cm: avgWingspanCm,
      avg_weight_g: avgWeightG,
      migratory: true,
      habitat: habitat,
    };

    post("/birds", { ...newBird })
      .then((result) => {
        console.log("AddBird", result);
        onAddBird(result);

        // setFamilyId();
        // setCommonName("");
        // setScientificName("");
        // setConservationStatus("");
        // setAvgWingspanCm("");
        // setAvgWeightG("");
        // setHabitat("");
      })
      .catch((error) => console.log(error));
  };

  // useEffect(() => {
  //   post("/birds", { bird: "Test" })
  //     .then((result) => {
  //       console.log("AddBird", result);
  //     })
  //     .catch((error) => console.log(error));
  // }, [post]);

  useEffect(() => {
    get("/families").then((newFamilies) => setFamilies(newFamilies?.data));
  }, [get]);

  if (loading) return <p className="text-center my-10">Loading</p>;

  return (
    <>
      <h2>Add bird</h2>
      <Select options={familyOptions} name="family_id" value={familyId} onChange={(value) => setFamilyId(value)} />
      <Input name="common_name" placeholder="Common Name" value={commonName} onChange={(e) => setCommonName(e.target.value)} />
      <Input name="scientific_name" placeholder="Scientific Name" value={scientificName} onChange={(e) => setScientificName(e.target.value)} />
      <Input
        maxLength={2}
        name="conservation_status"
        placeholder="Conservation Status"
        value={conservationStatus}
        onChange={(e) => setConservationStatus(e.target.value)}
      />
      <Input type="number" name="avg_wingspan_cm" placeholder="Wingspan" value={avgWingspanCm} onChange={(e) => setAvgWingspanCm(e.target.value)} />
      <Input type="number" name="avg_weight_g" placeholder="Weight" value={avgWeightG} onChange={(e) => setAvgWeightG(e.target.value)} />
      <Input name="habitat" placeholder="Habitiat" value={habitat} onChange={(e) => setHabitat(e.target.value)} />
      <Button onClick={handleSubmit}>Add Bird</Button>
    </>
  );
};
